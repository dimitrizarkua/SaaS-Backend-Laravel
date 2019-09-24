<?php

namespace App\Components\Contacts\Services;

use App\Components\Contacts\Events\NoteAttachedToContact;
use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\CompanyData;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\ContactData;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Contacts\Models\PersonData;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Tags\Enums\SpecialTags;
use App\Components\Tags\Enums\TagTypes;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Class ContactsService
 *
 * @package App\Components\Contacts\Services
 */
class ContactsService implements ContactsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getContact(int $contactId): Contact
    {
        return Contact::findOrFail($contactId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createPerson(PersonData $data): Contact
    {
        $contact = null;
        DB::transaction(function () use ($data, &$contact) {
            $contact = $this->createContact($data, ContactTypes::PERSON);
            ContactPersonProfile::create([
                'contact_id'   => $contact->id,
                'first_name'   => $data->getFirstName(),
                'last_name'    => $data->getLastName(),
                'job_title'    => $data->getJobTitle(),
                'direct_phone' => $data->getDirectPhone(),
                'mobile_phone' => $data->getMobilePhone(),
            ]);
        });

        return $contact;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createCompany(CompanyData $data): Contact
    {
        $contact = null;
        DB::transaction(function () use ($data, &$contact) {
            $contact = $this->createContact($data, ContactTypes::COMPANY);
            ContactCompanyProfile::create([
                'contact_id'                 => $contact->id,
                'legal_name'                 => $data->getLegalName(),
                'trading_name'               => $data->getTradingName(),
                'abn'                        => $data->getAbn(),
                'website'                    => $data->getWebsite(),
                'default_payment_terms_days' => $data->getDefaultPaymentTermsDays(),
            ]);
        });

        return $contact;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function deleteContact(int $contactId): void
    {
        $contact = $this->getContact($contactId);

        if ($contact->assignedJobs()->exists()) {
            throw new NotAllowedException('Could not delete contact assigned to a job');
        }
        if ($contact->subsidiaries()->exists()) {
            throw new NotAllowedException('Could not delete contact having assigned subsidiaries or persons');
        }
        $contact->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exceptions\Api\NotAllowedException
     */
    public function linkContacts(int $parentId, int $childId): void
    {
        $parent = $this->getContact($parentId);
        $child  = $this->getContact($childId);

        if (ContactTypes::PERSON === $parent->contact_type) {
            throw new NotAllowedException('Could not link contact to person');
        }
        if ($child->headoffices()->count() > 0) {
            $child->headoffices()->detach();
        }

        try {
            $parent->subsidiaries()->attach($child);
        } catch (Exception $e) {
            throw new NotAllowedException('Contact already added to this contact');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unlinkContacts(int $parentId, int $childId): void
    {
        $parent = $this->getContact($parentId);
        $child  = $this->getContact($childId);

        $parent->subsidiaries()->detach($child);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exceptions\Api\NotAllowedException
     * @throws \ReflectionException
     */
    public function addNote(int $contactId, int $noteId, int $meetingId = null): void
    {
        $contact = $this->getContact($contactId);

        try {
            $contact->notes()->attach($noteId, [
                'meeting_id' => $meetingId,
            ]);
        } catch (Exception $e) {
            throw new NotAllowedException('Note already added to this contact');
        }

        /** @var NotesServiceInterface $notesService */
        $notesService = app()->make(NotesServiceInterface::class);
        $note         = $notesService->getNote($noteId);

        $this->dispatchAddNoteEvents($contact, $note);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteNote(int $contactId, int $noteId): void
    {
        $contact = $this->getContact($contactId);

        $contact->notes()->updateExistingPivot($noteId, ['deleted_at' => 'now()']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exceptions\Api\NotAllowedException
     */
    public function addAddress(int $contactId, int $addressId, string $type): void
    {
        $contact = $this->getContact($contactId);

        try {
            $contact->addresses()->attach($addressId, [
                'type' => $type,
            ]);
            $contact->fresh()->searchable();
        } catch (Exception $e) {
            throw new NotAllowedException('Address already added to this contact');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAddress(int $contactId, int $addressId): void
    {
        $contact = $this->getContact($contactId);

        $contact->addresses()->detach($addressId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exceptions\Api\NotAllowedException
     */
    public function addTag(int $contactId, int $tagId): void
    {
        $contact = $this->getContact($contactId);

        try {
            $contact->tags()->attach($tagId);
        } catch (Exception $e) {
            throw new NotAllowedException('Tag already added to this contact');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTag(int $contactId, int $tagId): void
    {
        $contact = $this->getContact($contactId);

        $contact->tags()->detach($tagId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exceptions\Api\NotAllowedException
     */
    public function addManagedAccount(int $contactId, int $userId): void
    {
        $contact = $this->getContact($contactId);

        try {
            $contact->managedAccounts()->attach($userId);
        } catch (Exception $e) {
            throw new NotAllowedException('Managed account already exists');
        }

        $tag = $this->getManagedAccountTag();

        try {
            $this->addTag($contactId, $tag->id);
        } catch (Exception $e) {
            // Tag is already attached
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteManagedAccount(int $contactId, int $userId): void
    {
        $contact = $this->getContact($contactId);

        $contact->managedAccounts()->detach($userId);

        if (!$contact->managedAccounts()->exists()) {
            $tag = $this->getManagedAccountTag();
            $this->deleteTag($contactId, $tag->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function touch(int $contactId, Carbon $touchedAt = null): void
    {
        $contact = $this->getContact($contactId);

        $contact->last_active_at = null !== $touchedAt
            ? $touchedAt
            : Carbon::now();

        $contact->save();
    }

    /**
     * Get default contact status.
     *
     * @param int $contactCategoryId Contact category id.
     *
     * @return string
     */
    public function getDefaultStatus(int $contactCategoryId): string
    {
        /** @var \App\Components\Contacts\Models\ContactCategory $contactCategory */
        $contactCategory = ContactCategory::findOrFail($contactCategoryId);

        return $contactCategory->isCustomer()
            ? ContactStatuses::LEAD
            : ContactStatuses::ACTIVE;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function updateContactAvatar(int $contactId, UploadedFile $photo): Contact
    {
        $contact = $this->getContact($contactId);

        $photoService = $this->getPhotosService();
        if ($contact->avatar_photos_id) {
            $photoService->updatePhotoFromFile($contact->avatar_photos_id, $photo);
        } else {
            DB::transaction(function () use ($contact, $photo, $photoService) {
                $avatar                    = $photoService->createPhotoFromFile($photo);
                $contact->avatar_photos_id = $avatar->id;
                $contact->saveOrFail();
            });
        }

        $contact->refresh();

        return $contact;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function deleteContactAvatar(int $contactId)
    {
        $contact = $this->getContact($contactId);
        if (!$contact->avatar_photos_id) {
            throw new NotAllowedException('Avatar not set.');
        }

        $photoService = $this->getPhotosService();
        DB::transaction(function () use ($contact, $photoService) {
            $avatarId = $contact->avatar_photos_id;

            $contact->avatar_photos_id = null;
            $contact->saveOrFail();

            try {
                $photoService->deletePhoto($avatarId);
            } catch (\App\Components\Photos\Exceptions\NotAllowedException $exception) {
                // Perhaps photo is still attached to some other entity.
                // Do nothing in this case.
            }
        });
    }

    /**
     * @param \App\Components\Contacts\Models\ContactData $data
     * @param string                                      $contactType
     *
     * @return \App\Components\Contacts\Models\Contact
     *
     * @throws \Throwable
     */
    private function createContact(ContactData $data, string $contactType): Contact
    {
        $contact = DB::transaction(function () use ($data, $contactType) {
            $defaultStatus = $this->getDefaultStatus($data->getContactCategoryId());
            $contact       = Contact::create([
                'contact_type'        => $contactType,
                'contact_category_id' => $data->getContactCategoryId(),
                'email'               => $data->getEmail(),
                'business_phone'      => $data->getBusinessPhone(),
            ]);
            $contact->changeStatus($defaultStatus);

            return $contact;
        });

        return $contact;
    }

    /**
     * @return \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface
     */
    private function getNotificationService(): UserNotificationsServiceInterface
    {
        return app()->make(UserNotificationsServiceInterface::class);
    }

    /**
     * @param \App\Components\Contacts\Models\Contact $contact
     * @param \App\Components\Notes\Models\Note       $note
     *
     * @throws \ReflectionException
     */
    private function dispatchAddNoteEvents(Contact $contact, Note $note): void
    {
        event(new NoteAttachedToContact($contact, $note));
        $this->getNotificationService()
            ->dispatchUserMentionedEvent($contact, $note, $note->user_id);
    }

    /**
     * @return \App\Components\Tags\Models\Tag
     */
    private function getManagedAccountTag(): Tag
    {
        return Tag::firstOrCreate([
            'name' => SpecialTags::MANAGED_ACCOUNT,
            'type' => TagTypes::CONTACT,
        ]);
    }

    /**
     * @return \App\Components\Photos\Interfaces\PhotosServiceInterface
     */
    private function getPhotosService(): PhotosServiceInterface
    {
        return app()->make(PhotosServiceInterface::class);
    }
}
