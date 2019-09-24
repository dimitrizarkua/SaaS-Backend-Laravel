<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\PurchaseOrderInfoInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\Filters\PurchaseOrderListingFilter;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\TaxRate;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class PurchaseOrderListingServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   purchase-orders
 * @group   finance
 * @group   finance-listings
 */
class PurchaseOrderListingServiceTest extends TestCase
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(PurchaseOrderListingServiceInterface::class);
        $models        = [
            PurchaseOrderItem::class,
            GSCode::class,
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

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testGetInfoReturnRightData()
    {
        /** @var LocationUser $locationUser */
        $locationUser             = factory(LocationUser::class)->create();
        $countOfDraftOrders       = $this->faker->numberBetween(1, 5);
        $totalSumOfDraftOrders    = 0;
        $countOfPendingOrders     = $this->faker->numberBetween(1, 5);
        $totalSumOfPendingOrders  = 0;
        $countOfApprovedOrders    = $this->faker->numberBetween(1, 5);
        $totalSumOfApprovedOrders = 0;
        $draftOrders              = factory(PurchaseOrder::class, $countOfDraftOrders)->create([
            'location_id' => $locationUser->location_id,
        ]);
        $pendingOrders            = factory(PurchaseOrder::class, $countOfPendingOrders)->create([
            'location_id' => $locationUser->location_id,
            'locked_at'   => Carbon::now(),
        ]);
        $approvedOrders           = factory(PurchaseOrder::class, $countOfApprovedOrders)->create([
            'location_id' => $locationUser->location_id,
            'locked_at'   => Carbon::now(),
        ]);
        foreach ($draftOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            $totalSumOfDraftOrders += $purchaseOrder->getTotalAmount();
        }
        foreach ($pendingOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->approveRequests()->create([
                'requester_id' => $locationUser->user_id,
                'approver_id'  => factory(User::class)->create()->id,
            ]);
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            $totalSumOfPendingOrders += $purchaseOrder->getTotalAmount();
        }
        foreach ($approvedOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->statuses()->create([
                'user_id' => $locationUser->user_id,
                'status'  => FinancialEntityStatuses::APPROVED,
            ]);
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            $totalSumOfApprovedOrders += $purchaseOrder->getTotalAmount();
        }

        $info            = $this->service->getInfo([$locationUser->location_id]);
        $draft           = $info->getDraftCounter();
        $pendingApproval = $info->getPendingApprovalCounter();
        $approved        = $info->getApprovedCounter();

        self::assertEquals($countOfDraftOrders, $draft->count);
        self::assertEquals($countOfPendingOrders, $pendingApproval->count);
        self::assertEquals($countOfApprovedOrders, $approved->count);
        self::assertEquals($totalSumOfDraftOrders, $draft->amount);
        self::assertEquals($totalSumOfPendingOrders, $pendingApproval->amount);
        self::assertEquals($totalSumOfApprovedOrders, $approved->amount);
    }

    public function testGetInfoWithFiltration()
    {
        /** @var Location $firstLocation */
        $firstLocation = factory(Location::class)->create();

        $countOfDraftForFirstLocation    = $this->faker->numberBetween(1, 5);
        $countOfPendingForFirstLocation  = $this->faker->numberBetween(1, 5);
        $countOfApprovedForFirstLocation = $this->faker->numberBetween(1, 5);

        $this->createOrders(
            $firstLocation,
            $countOfDraftForFirstLocation,
            $countOfPendingForFirstLocation,
            $countOfApprovedForFirstLocation
        );

        /** @var Location $firstLocation */
        $secondLocation = factory(Location::class)->create();

        $countOfDraftForSecondLocation    = $this->faker->numberBetween(1, 5);
        $countOfPendingForSecondLocation  = $this->faker->numberBetween(1, 5);
        $countOfApprovedForSecondLocation = $this->faker->numberBetween(1, 5);

        $this->createOrders(
            $secondLocation,
            $countOfDraftForSecondLocation,
            $countOfPendingForSecondLocation,
            $countOfApprovedForSecondLocation
        );

        $info            = $this->service->getInfo([$firstLocation->id]);
        $draft           = $info->getDraftCounter();
        $pendingApproval = $info->getPendingApprovalCounter();
        $approved        = $info->getApprovedCounter();

        self::assertInstanceOf(PurchaseOrderInfoInterface::class, $info);
        self::assertEquals($countOfDraftForFirstLocation, $draft->count);
        self::assertEquals($countOfPendingForFirstLocation, $pendingApproval->count);
        self::assertEquals($countOfApprovedForFirstLocation, $approved->count);
    }

    private function createOrders(
        Location $location,
        int $countOfDraft,
        int $countOfPending,
        int $countOfApproved
    ): void {
        // Draft
        factory(PurchaseOrder::class, $countOfDraft)->create([
            'location_id' => $location->id,
        ]);

        // Pending approval
        factory(PurchaseOrder::class, $countOfPending)
            ->create([
                'location_id' => $location->id,
                'locked_at'   => Carbon::now(),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) {
                $purchaseOrder->approveRequests()->create([
                    'requester_id' => factory(User::class)->create()->id,
                    'approver_id'  => factory(User::class)->create()->id,
                ]);
                factory(PurchaseOrderItem::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                ]);
            });

        // Approved
        factory(PurchaseOrder::class, $countOfApproved)
            ->create([
                'location_id' => $location->id,
                'locked_at'   => Carbon::now(),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) use ($location) {
                $purchaseOrder->statuses()->create([
                    'user_id' => factory(User::class)->create()->id,
                    'status'  => FinancialEntityStatuses::APPROVED,
                ]);
                factory(PurchaseOrderItem::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                ]);
            });
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetDraftOrdersWithFilterReturnRightData()
    {
        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var Job $job */
        $job                = factory(Job::class)->create();
        $date               = $this->faker->date();
        $filter             = new PurchaseOrderListingFilter([
            'locations'            => [$locationUser->location_id],
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date_from'            => Carbon::createFromFormat('Y-m-d', $date)->subDay(),
            'date_to'              => Carbon::createFromFormat('Y-m-d', $date)->addDay(),
        ]);
        $attributes         = [
            'location_id'          => $locationUser->location_id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
        ];
        $countOfDraftOrders = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfDraftOrders)->create($attributes);

        $reloadedOrders = $this->service->getDraftPurchaseOrders($filter);
        self::assertCount($countOfDraftOrders, $reloadedOrders);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetPendingApprovalOrdersWithFilterReturnRightData()
    {
        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var Job $job */
        $job                  = factory(Job::class)->create();
        $date                 = $this->faker->date();
        $filter               = new PurchaseOrderListingFilter([
            'locations'            => [$locationUser->location_id],
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date_from'            => Carbon::createFromFormat('Y-m-d', $date)->subDay(),
            'date_to'              => Carbon::createFromFormat('Y-m-d', $date)->addDay(),
        ]);
        $attributes           = [
            'location_id'          => $locationUser->location_id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
            'locked_at'            => Carbon::now(),
        ];
        $countOfPendingOrders = $this->faker->numberBetween(1, 5);
        $pendingOrders        = factory(PurchaseOrder::class, $countOfPendingOrders)->create($attributes);
        foreach ($pendingOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->approveRequests()->create([
                'requester_id' => $locationUser->user_id,
                'approver_id'  => factory(User::class)->create()->id,
            ]);
        }

        $reloadedOrders = $this->service->getPendingApprovalPurchaseOrders($filter);
        self::assertCount($countOfPendingOrders, $reloadedOrders);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetApprovedOrdersWithFilterReturnRightData()
    {
        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var Job $job */
        $job                   = factory(Job::class)->create();
        $date                  = $this->faker->date();
        $filter                = new PurchaseOrderListingFilter([
            'locations'            => [$locationUser->location_id],
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date_from'            => Carbon::createFromFormat('Y-m-d', $date)->subDay(),
            'date_to'              => Carbon::createFromFormat('Y-m-d', $date)->addDay(),
        ]);
        $attributes            = [
            'location_id'          => $locationUser->location_id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
            'locked_at'            => Carbon::now(),
        ];
        $countOfApprovedOrders = $this->faker->numberBetween(1, 5);
        $approvedOrders        = factory(PurchaseOrder::class, $countOfApprovedOrders)->create($attributes);
        foreach ($approvedOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->statuses()->create([
                'user_id' => $locationUser->user_id,
                'status'  => FinancialEntityStatuses::APPROVED,
            ]);
        }

        $reloadedOrders = $this->service->getApprovedPurchaseOrders($filter);
        self::assertCount($countOfApprovedOrders, $reloadedOrders);
    }
}
