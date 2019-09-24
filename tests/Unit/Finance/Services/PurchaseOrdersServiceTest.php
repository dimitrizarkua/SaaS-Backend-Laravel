<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\VO\CreatePurchaseOrderData;
use App\Components\Finance\Services\PurchaseOrdersService;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

/**
 * Class PurchaseOrdersServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrdersServiceTest extends TestCase
{
    /**
     * @var PurchaseOrdersService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(PurchaseOrdersService::class);
        $models        = [
            PurchaseOrder::class,
            AccountType::class,
            GLAccount::class,
            TaxRate::class,
            AccountingOrganization::class,
            AccountType::class,
            Location::class,
        ];
        $this->models  = array_merge($models, $this->models);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testGetPurchaseOrder(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();

        $reloaded = $this->service->getEntity($purchaseOrder->id);

        self::assertEquals($purchaseOrder->location_id, $reloaded->location_id);
        self::assertEquals($purchaseOrder->accounting_organization_id, $reloaded->accounting_organization_id);
        self::assertEquals($purchaseOrder->recipient_contact_id, $reloaded->recipient_contact_id);
        self::assertEmpty($purchaseOrder->job_id);
        self::assertEmpty($purchaseOrder->document_id);
        self::assertEquals($purchaseOrder->date, $reloaded->date);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToGetNotExistingPurchaseOrder(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->getEntity(0);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCreatePurchaseOrder(): void
    {
        /** @var User $user */
        $user    = factory(User::class)->create();
        $lockDay = $this->faker->numberBetween(1, 15);

        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => $lockDay,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $attributes = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'date'                 => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ];

        $data          = new CreatePurchaseOrderData($attributes);
        $purchaseOrder = $this->service->create($data, $user->id);
        /** @var PurchaseOrder $reloaded */
        $reloaded = PurchaseOrder::findOrFail($purchaseOrder->id);

        self::assertEquals($attributes['location_id'], $reloaded->location_id);
        self::assertEquals($accountingOrganization->id, $reloaded->accounting_organization_id);
        self::assertEquals($attributes['recipient_contact_id'], $reloaded->recipient_contact_id);
        self::assertEmpty($reloaded->job_id);
        self::assertEmpty($reloaded->document_id);
        self::assertEquals($attributes['date'], $reloaded->date->toDateString());

        $status = PurchaseOrderStatus::query()->where([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $user->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ])->first();
        self::assertNotNull($status);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreatePurchaseOrderWhenNoActiveAccountingOrganizations(): void
    {
        /** @var User $user */
        $user       = factory(User::class)->create();
        $lockDay    = $this->faker->numberBetween(1, 15);
        $attributes = [
            'location_id'                => factory(Location::class)->create()->id,
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'recipient_contact_id'       => factory(Contact::class)->create()->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->subDay()
                ->toDateString(),
        ];
        $data       = new CreatePurchaseOrderData($attributes);
        Carbon::setTestNow(Carbon::create(null, null, $lockDay));

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('For given location there is no any active Accounting Organization.');
        $this->service->create($data, $user->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreatePurchaseOrderWhenDateIsOutOfFinancialMonth(): void
    {
        $lockDay  = $this->faker->numberBetween(1, 15);
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => $lockDay,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location->id);
        /** @var User $user */
        $user       = factory(User::class)->create();
        $attributes = [
            'location_id'                => $location->id,
            'accounting_organization_id' => $accountingOrganization->id,
            'recipient_contact_id'       => factory(Contact::class)->create()->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->subDay()
                ->toDateString(),
        ];
        $data       = new CreatePurchaseOrderData($attributes);
        Carbon::setTestNow(Carbon::create(null, null, $lockDay));

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Purchase order can only be created if it\'s date is after the end-of-month financial date.'
        );
        $this->service->create($data, $user->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdatePurchaseOrder(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        $attributes = [
            'job_id' => factory(Job::class)->create()->id,
            'date'   => Carbon::now()->subDays(1)->toDateString(),
        ];

        $this->service->update($purchaseOrder->id, $attributes);

        $reloaded = PurchaseOrder::findOrFail($purchaseOrder->id);

        self::assertEquals($attributes['job_id'], $reloaded->job_id);
        self::assertEquals($attributes['date'], $reloaded->date->toDateString());
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateApprovedPurchaseOrder(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => Carbon::now(),
        ]);
        $attributes    = [
            'date' => $this->faker->date(),
        ];
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('You can\'t update the purchase order because it has been already approved.');
        $this->service->update($purchaseOrder->id, $attributes);
    }

    /**
     * @throws \Throwable
     */
    public function testDeletePurchaseOrder(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        $this->service->delete($purchaseOrder->id);

        $this->expectException(ModelNotFoundException::class);
        PurchaseOrder::findOrFail($purchaseOrder->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDeleteApprovedPurchaseOrder(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => Carbon::now(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('You can\'t delete the purchase order because it has already been approved.');
        $this->service->delete($purchaseOrder->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDeletePurchaseOrderWithApprovalRequests(): void
    {
        /** @var PurchaseOrderApproveRequest $purchaseOrderApproveRequest */
        $purchaseOrderApproveRequest = factory(PurchaseOrderApproveRequest::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrderApproveRequest->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Purchase order can\'t be deleted because it can\'t be modified or has approve requests.'
        );
        $this->service->delete($purchaseOrderApproveRequest->purchase_order_id);
    }

    /**
     * @throws \Throwable
     */
    public function testCreatePurchaseOrderApproveRequest(): void
    {
        $lockDay          = $this->faker->numberBetween(1, 15);
        $countOfApprovers = $this->faker->numberBetween(1, 5);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        factory(PurchaseOrderItem::class, 3)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        /** @var \Illuminate\Support\Collection $approvers */
        $approvers   = factory(User::class, $countOfApprovers)
            ->create([
                'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
            ])
            ->each(function (User $user) use ($purchaseOrder) {
                $user->locations()->attach($purchaseOrder->location_id);
            });
        $approverIds = $approvers->pluck('id')->toArray();
        /** @var User $requester */
        $requester = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);

        $this->service->createApproveRequest($purchaseOrder->id, $requester->id, $approverIds);

        $approveRequests = PurchaseOrderApproveRequest::query()
            ->whereIn('approver_id', $approverIds)
            ->where([
                'purchase_order_id' => $purchaseOrder->id,
                'requester_id'      => $requester->id,
            ])->get();
        self::assertCount($countOfApprovers, $approveRequests);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToCreatePurchaseOrderApproveRequestWhenOrderAlreadyApproved(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => $this->faker->dateTime(),
        ]);
        $approverIds   = [factory(User::class)->create()->id];

        factory(PurchaseOrderItem::class, 3)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        /** @var User $requester */
        $requester = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);
        PurchaseOrderStatus::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $requester->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('You can\'t approve the purchase order because it has already been approved.');
        $this->service->createApproveRequest(
            $purchaseOrder->id,
            $requester->id,
            $approverIds
        );
    }

    /**
     * @throws \Throwable
     */
    public function testFailToCreatePurchaseOrderApproveRequestWhenDateIsOutOfFinancialMonth(): void
    {
        $lockDay = $this->faker->numberBetween(1, 15);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->subDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        factory(PurchaseOrderItem::class, 3)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        Carbon::setTestNow(Carbon::create(null, null, $lockDay));
        $approverIds = [factory(User::class)->create()->id];
        /** @var User $requester */
        $requester = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Purchase order can only be approved if it\'s date is after the end-of-month financial date.'
        );
        $this->service->createApproveRequest(
            $purchaseOrder->id,
            $requester->id,
            $approverIds
        );
    }

    /**
     * @throws \Throwable
     */
    public function testApprovePurchaseOrder(): void
    {
        $lockDay = $this->faker->numberBetween(1, 15);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        factory(PurchaseOrderItem::class, 3)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);
        $user->locations()->attach($purchaseOrder->location_id);

        $this->service->approve($purchaseOrder->id, $user);
        $reloaded = PurchaseOrder::findOrFail($purchaseOrder->id);

        self::assertTrue($reloaded->isApproved());
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApprovePurchaseOrderWithZeroBalance(): void
    {
        $lockDay = $this->faker->numberBetween(1, 15);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'unit_cost'         => 0,
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);
        $user->locations()->attach($purchaseOrder->location_id);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Unable to approve purchase order with zero balance');
        $this->service->approve($purchaseOrder->id, $user);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApprovePurchaseOrderWhenUserHasNotEnoughApproveLimit(): void
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderItem->purchase_order_id);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount() - 1,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'User [%d] can\'t be an approver of purchase order [%d].',
            $user->id,
            $purchaseOrder->id
        ));
        $this->service->approve($purchaseOrder->id, $user);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApprovePurchaseOrderWhenItIsAlreadyApproved(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => $this->faker->dateTime(),
        ]);
        factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'unit_cost'         => $this->faker->numberBetween(1, 50),
            'quantity'          => 1,
        ]);
        /** @var User $user */
        $user = factory(User::class)->create([
            'purchase_order_approve_limit' => $purchaseOrder->getTotalAmount(),
        ]);
        PurchaseOrderStatus::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $user->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Unable to change purchase order status.');
        $this->service->approve($purchaseOrder->id, $user);
    }

    /**
     * @throws \Throwable
     */
    public function testGetSuggestedApprovers(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder   = factory(PurchaseOrder::class)->create();
        $numberOfRecords = $this->faker->numberBetween(1, 5);
        factory(LocationUser::class, $numberOfRecords)->create([
            'location_id' => $purchaseOrder->location_id,
            'user_id'     => function () {
                return factory(User::class)->create([
                    'purchase_order_approve_limit' => $this->faker->numberBetween(1, 50),
                ])->id;
            },
        ]);

        $users = $this->service->getSuggestedApprovers($purchaseOrder->id);

        self::assertCount($numberOfRecords, $users);
    }
}
