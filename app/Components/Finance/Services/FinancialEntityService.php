<?php

namespace App\Components\Finance\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Utils\HtmlToPDFConverter;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\VO\CreateFinancialEntityData;
use App\Models\User;
use App\Utils\FileIO;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Class FinancialEntityService
 *
 * @package App\Components\Finance\Services
 */
abstract class FinancialEntityService
{
    protected const EVENT_NAME_APPROVED                = 'approved';
    protected const EVENT_NAME_CREATED                 = 'created';
    protected const EVENT_NAME_DELETED                 = 'deleted';
    protected const EVENT_NAME_APPROVE_REQUEST_CREATED = 'approve_request_created';
    protected const EVENT_NAME_UPDATED                 = 'updated';

    /**
     * Class name of view data.
     *
     * @var string
     */
    protected $viewDataClass;
    /**
     * Name of template for print version generation.
     *
     * @var string
     */
    protected $templateName;
    /**
     * Document service.
     *
     * @var \App\Components\Documents\Interfaces\DocumentsServiceInterface
     */
    protected $documentService;
    /**
     * Accounting Organization service instance.
     *
     * @var AccountingOrganizationsServiceInterface
     */
    protected $accountingOrganizationsService;

    /**
     * FinancialEntityService constructor.
     *
     * @param DocumentsServiceInterface               $documentService
     * @param AccountingOrganizationsServiceInterface $accountingOrganizationsService
     */
    public function __construct(
        DocumentsServiceInterface $documentService,
        AccountingOrganizationsServiceInterface $accountingOrganizationsService
    ) {
        $this->documentService                = $documentService;
        $this->accountingOrganizationsService = $accountingOrganizationsService;
    }

    /**
     * Returns entity by its id.
     *
     * @param int $entityId
     *
     * @return FinancialEntity|\Eloquent
     * @throws \Throwable
     */
    public function getEntity(int $entityId): FinancialEntity
    {
        return call_user_func([$this->getEntityClass(), 'findOrFail'], $entityId);
    }

    /**
     * Creates draft entity.
     *
     * @param CreateFinancialEntityData $data Data.
     * @param int|null                  $userId
     *
     * @return FinancialEntity
     * @throws \Throwable
     */
    public function create(CreateFinancialEntityData $data, int $userId = null): FinancialEntity
    {
        $this->checkCreationData($data);

        $className = $this->getEntityClass();
        // Disable search syncing until transaction not committed
        call_user_func([$className, 'disableSearchSyncing']);

        /** @var FinancialEntity|Model $entity */
        $entity = DB::transaction(function () use ($data, $userId, $className) {
            $entityData = $data->toArray();

            $entityData['accounting_organization_id'] = $this->getAccountingOrganization($data->getLocationId())->id;
            /** @var FinancialEntity|Model $entity */
            $entity = new $className($entityData);

            $entity->saveOrFail();
            $entity->statuses()->create([
                'status'  => FinancialEntityStatuses::DRAFT,
                'user_id' => $userId,
            ]);

            $this->createItems($entity, $data);

            return $entity;
        });

        call_user_func([$className, 'enableSearchSyncing']);
        $entity->searchable();

        $this->fireEvent(self::EVENT_NAME_CREATED, [$entity]);

        return $entity;
    }

    /**
     * Updates financial entity.
     *
     * @param int   $entityId    Entity id to be updated.
     * @param array $data        Data to update entity.
     * @param bool  $forceUpdate Force update entity. This flag allows to update locked entity.
     *
     * @return FinancialEntity
     * @throws \Throwable
     */
    public function update(int $entityId, array $data, bool $forceUpdate = false): FinancialEntity
    {
        $entity = $this->getEntity($entityId);
        if ($entity->isApproved()) {
            throw new NotAllowedException(sprintf(
                'You can\'t update the %s because it has been already approved.',
                $this->getHumanReadableName(false)
            ));
        }
        if (false === $forceUpdate && $entity->isLocked()) {
            throw new NotAllowedException(sprintf(
                'You can\'t update the %s because it has been locked.',
                $this->getHumanReadableName(false)
            ));
        }

        if (isset($data['date'])) {
            $date = new Carbon($data['date']);

            $isDateInFinancialMonth = $entity->accountingOrganization->isDateWithinCurrentFinancialMonth($date);
            if (!$isDateInFinancialMonth) {
                throw new NotAllowedException(
                    'Selected date is earlier than end-of-month financial date.'
                );
            }
        }

        $protectedFields = ['location_id', 'document_id', 'document_id', 'accounting_organization_id'];
        foreach ($protectedFields as $field) {
            unset($data[$field]);
        }

        if (isset($data['recipient_contact_id'])) {
            $contact = Contact::findOrFail($data['recipient_contact_id']);

            $address = $contact->getAddress();
            if (null === $address) {
                throw new NotAllowedException('Recipient contact should has at least one attached address.');
            }

            $data['recipient_address'] = $address->full_address;
            $data['recipient_name']    = $contact->getContactName();
        }

        $entity->update($data);
        $this->fireEvent(self::EVENT_NAME_UPDATED, [$entity]);

        return $entity;
    }

    /**
     * There should be placed some additional logic of creation of entity if any.
     * Note: This method runs inside transaction.
     *
     * @param FinancialEntity           $entity
     * @param CreateFinancialEntityData $data
     *
     * @throws \Throwable
     */
    protected function createItems(FinancialEntity $entity, CreateFinancialEntityData $data): void
    {
        foreach ($data->getItems() as $item) {
            $itemsClass = $this->getItemsClassName();
            /** @var Model $itemModel */
            $itemModel               = new $itemsClass($item->toArray());
            $foreignId               = $this->getForeignKeyName() . '_id';
            $itemModel->{$foreignId} = $entity->id;
            $itemModel->saveOrFail();
        }
    }

    /**
     * Returns class name of related items model.
     *
     * @return string
     */
    abstract protected function getItemsClassName(): string;

    /**
     * Checks whether is data eligible.
     *
     * @param CreateFinancialEntityData $data Data to be checked.
     *
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     */
    protected function checkCreationData(CreateFinancialEntityData $data): void
    {
        $accountingOrganization = $this->getAccountingOrganization($data->getLocationId());

        $isDateInFinancialMonth = $accountingOrganization->isDateWithinCurrentFinancialMonth($data->getDate());
        if (!$isDateInFinancialMonth) {
            throw new NotAllowedException(sprintf(
                '%s can only be created if it\'s date is after the end-of-month financial date.',
                $this->getHumanReadableName()
            ));
        }
    }

    /**
     * Returns accounting organization by given location location.
     *
     * @param int $locationId
     *
     * @return \App\Components\Finance\Models\AccountingOrganization
     */
    protected function getAccountingOrganization(int $locationId): AccountingOrganization
    {
        $accountingOrganization = $this->accountingOrganizationsService
            ->findActiveAccountOrganizationByLocation($locationId);

        if (null === $accountingOrganization) {
            throw new NotAllowedException('For given location there is no any active Accounting Organization.');
        }

        return $accountingOrganization;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     * @throws NotAllowedException If one of approver is not able to approve.
     * @throws NotAllowedException If entity is already approved.
     */
    public function createApproveRequest(int $entityId, int $requesterId, array $approverIdsList): void
    {
        $entity = $this->getEntity($entityId);

        if (true === $entity->isApproved()) {
            throw new NotAllowedException(sprintf(
                'You can\'t approve the %s because it has already been approved.',
                $this->getHumanReadableName(false)
            ));
        }

        $isDateInFinancialMonth = $entity->getAccountingOrganization()
            ->isDateWithinCurrentFinancialMonth($entity->getDate());
        if (false === $isDateInFinancialMonth) {
            throw new NotAllowedException(sprintf(
                '%s can only be approved if it\'s date is after the end-of-month financial date.',
                $this->getHumanReadableName()
            ));
        }

        $approverList = User::query()
            ->whereIn('id', $approverIdsList)
            ->with('locations')
            ->get();

        $data = [];

        $existingApproveRequests = $entity->approveRequests->keyBy('approver_id');
        foreach ($approverList as $approver) {
            $canUserBeApprover = $this->canUserBeApprover($entity, $approver);
            if (false === $canUserBeApprover) {
                throw new NotAllowedException(sprintf(
                    'User [%d] can\'t be an approver.',
                    $approver->id
                ));
            }

            $foreignKeyId = $this->getForeignKeyName() . '_id';
            if (false === $existingApproveRequests->has($approver->id)) {
                $data[] = [
                    $foreignKeyId  => $entityId,
                    'approver_id'  => $approver->id,
                    'requester_id' => $requesterId,
                ];
            }
        }

        $entity->lockUp();
        call_user_func([$this->getApproveRequestClass(), 'insert'], $data);
        $this->fireEvent(self::EVENT_NAME_APPROVE_REQUEST_CREATED, [$entity, $requesterId]);
    }

    /**
     * @inheritdoc
     *
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function approve(int $entityId, User $user): void
    {
        $entity = $this->getEntity($entityId);
        $status = FinancialEntityStatuses::APPROVED;

        if (!$entity->canBeApproved()) {
            throw new NotAllowedException(sprintf(
                'Unable to approve %s with zero balance.',
                $this->getHumanReadableName(false)
            ));
        }
        if (false === $entity->getLatestStatus()->canBeChangedTo($status)) {
            throw new NotAllowedException(sprintf(
                'Unable to change %s status.',
                $this->getHumanReadableName(false)
            ));
        }

        if (false === $this->canUserBeApprover($entity, $user)) {
            throw new NotAllowedException(
                sprintf(
                    'User [%d] can\'t be an approver of %s [%d].',
                    $user->id,
                    $this->getHumanReadableName(false),
                    $entityId
                )
            );
        }

        DB::transaction(function () use ($status, $entity, $user) {
            $entity->lockUp();

            $entity->statuses()->create([
                'status'  => $status,
                'user_id' => $user->id,
            ]);

            $entity->approveRequests()
                ->where('approver_id', $user->id)
                ->update([
                    'approved_at' => Carbon::now(),
                ]);

            $entity->approveRequests()
                ->where('approver_id', '!=', $user->id)
                ->delete();

            $this->afterApprove($entity);
        });

        $this->fireEvent(self::EVENT_NAME_APPROVED, [$entity]);
    }

    /**
     * Additional logic after approve entity.
     *
     * @param FinancialEntity $entity
     */
    protected function afterApprove(FinancialEntity $entity): void
    {
    }

    /**
     * @param int $entityId
     *
     * @throws \Throwable
     */
    public function delete(int $entityId): void
    {
        $entity = $this->getEntity($entityId);
        if ($entity->isApproved()) {
            throw new NotAllowedException(sprintf(
                'You can\'t delete the %s because it has already been approved.',
                $this->getHumanReadableName(false)
            ));
        }
        if (false === $entity->canBeDeleted()) {
            throw new NotAllowedException(sprintf(
                '%s can\'t be deleted because it can\'t be modified or has approve requests.',
                $this->getHumanReadableName()
            ));
        }

        $entity->delete();

        $this->fireEvent(self::EVENT_NAME_DELETED, [$entity]);
    }

    /**
     * Generate print version of the entity. Print version will be saved as document.
     *
     * @param int $entityId Entity id.
     *
     * @throws \Throwable
     */
    public function generateDocument(int $entityId): void
    {
        if (null === $this->viewDataClass) {
            throw new RuntimeException('You must redefine \'viewDataClass\' property of class.');
        }

        if (null === $this->templateName) {
            throw new RuntimeException('You must redefine \'templateName\' property of class.');
        }

        DB::transaction(function () use ($entityId) {
            $entity          = $this->getEntity($entityId);
            $previousVersion = $entity->getDocumentId();

            $viewDataClass = $this->viewDataClass;
            $viewData      = new $viewDataClass($entity);
            $filename      = sprintf(
                '%s#%d(%s).pdf',
                $this->getForeignKeyName(),
                $entityId,
                $entity->getCreatedAt()->format('d-m-Y')
            );

            $filePath = FileIO::getTmpFilePath($filename);

            $converter = new HtmlToPDFConverter($viewData, $this->templateName);
            $converter->convert($filePath);

            $fileInstance        = new UploadedFile($filePath, $filename);
            $document            = $this->documentService->createDocumentFromFile($fileInstance);
            $entity->document_id = $document->id;
            $entity->saveOrFail();
            if (null !== $previousVersion) {
                try {
                    $this->documentService->deleteDocument($previousVersion, true);
                } catch (Exception $e) {
                    // That means document doesn't exists in the storage. Skipping this error
                }
            }

            //Delete tmp file
            File::delete($filePath);
        });
    }

    /**
     * Fire an event if last one exist in events map.
     *
     * @param string $methodName
     * @param array  $args
     */
    private function fireEvent(string $methodName, array $args): void
    {
        $events = $this->getEventsMap();

        if (!array_key_exists($methodName, $events)) {
            return;
        }

        $className = $events[$methodName];
        $event     = new $className(...$args);
        event($event);
    }

    /**
     * Returns map of method name and corresponding event class.
     * Key is service method name, value is class name of event that should be fired.
     *
     * @return array
     */
    protected function getEventsMap(): array
    {
        return [];
    }

    /**
     * Checks whether the user can be approver of a financial entity.
     *
     * @param FinancialEntity $entity
     * @param User            $user
     *
     * @return bool
     */
    protected function canUserBeApprover(FinancialEntity $entity, User $user): bool
    {
        $userLocation = $user->locations
            ->where('id', $entity->getLocationId())
            ->first();

        if (null === $userLocation) {
            return false;
        }

        return $this->isUserHasCorrectLimit($entity, $user);
    }

    /**
     * Returns human readable name for entity.
     *
     * @param bool $ucFirst Indicates if first character should be in uppercase.
     *
     * @return string
     */
    protected function getHumanReadableName(bool $ucFirst = true): string
    {
        $name = Str::snake(class_basename($this->getEntityClass()), ' ');

        return $ucFirst ? ucFirst($name) : $name;
    }

    /**
     * Returns class name of entity which service is working with.
     *
     * @return string
     */
    abstract protected function getEntityClass(): string;

    /**
     * Returns class name of approve request instance.
     *
     * @return string
     */
    abstract protected function getApproveRequestClass(): string;

    /**
     * Returns foreign key name.
     *
     * @return string
     */
    abstract protected function getForeignKeyName(): string;

    /**
     * Checks whether is user has correct limit to be approver of entity.
     *
     * @param FinancialEntity $entity
     * @param User            $user
     *
     * @return bool
     */
    abstract protected function isUserHasCorrectLimit(FinancialEntity $entity, User $user): bool;
}
