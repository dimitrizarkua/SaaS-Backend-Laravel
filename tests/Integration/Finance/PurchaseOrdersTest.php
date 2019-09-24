<?php

namespace Tests\Integration\Finance;

use App\Components\Finance\Enums\PurchaseOrderCountersCacheKeys;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Components\Finance\DataProviders\CountersDataProvider;

/**
 * Class PurchaseOrdersTest
 *
 * @package Tests\Integration\Finance
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrdersTest extends TestCase
{
    /**
     * @var PurchaseOrderCountersDataProviderInterface|CountersDataProvider
     */
    private $countersDataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->countersDataProvider = $this->app->make(PurchaseOrderCountersDataProviderInterface::class);
        $models                     = [
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
        $this->models               = array_merge($models, $this->models);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->countersDataProvider);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testRecalculateCounters()
    {
        /** @var Location $location */
        $location           = factory(Location::class)->create();
        $draftCounterKey    = sprintf(
            PurchaseOrderCountersCacheKeys::COUNTER_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_DRAFT,
            $location->id
        );
        $draftAmountKey     = sprintf(
            PurchaseOrderCountersCacheKeys::AMOUNT_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_DRAFT,
            $location->id
        );
        $pendingCounterKey  = sprintf(
            PurchaseOrderCountersCacheKeys::COUNTER_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_PENDING,
            $location->id
        );
        $pendingAmountKey   = sprintf(
            PurchaseOrderCountersCacheKeys::AMOUNT_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_PENDING,
            $location->id
        );
        $approvedCounterKey = sprintf(
            PurchaseOrderCountersCacheKeys::COUNTER_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_APPROVED,
            $location->id
        );
        $approvedAmountKey  = sprintf(
            PurchaseOrderCountersCacheKeys::AMOUNT_KEY_FORMAT,
            PurchaseOrderCountersCacheKeys::CACHE_TYPE_APPROVED,
            $location->id
        );

        $this->countersDataProvider->recalculateCounters([$location->id]);

        $countOfDraftOrders    = $this->faker->numberBetween(1, 5);
        $countOfPendingOrders  = $this->faker->numberBetween(1, 5);
        $countOfApprovedOrders = $this->faker->numberBetween(1, 5);
        $draftOrders           = factory(PurchaseOrder::class, $countOfDraftOrders)->create([
            'location_id' => $location->id,
        ]);
        $pendingOrders         = factory(PurchaseOrder::class, $countOfPendingOrders)->create([
            'location_id' => $location->id,
            'locked_at'   => Carbon::now(),
        ]);
        $approvedOrders        = factory(PurchaseOrder::class, $countOfApprovedOrders)->create([
            'location_id' => $location->id,
            'locked_at'   => Carbon::now(),
        ]);
        foreach ($draftOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
        }
        foreach ($pendingOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            factory(PurchaseOrderApproveRequest::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
        }
        foreach ($approvedOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            factory(PurchaseOrderStatus::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
                'status'            => FinancialEntityStatuses::APPROVED,
            ]);
            factory(PurchaseOrderItem::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
            ]);
        }

        $draftCounterCache    = Cache::get($draftCounterKey);
        $draftAmountCache     = Cache::get($draftAmountKey);
        $pendingCounterCache  = Cache::get($pendingCounterKey);
        $pendingAmountCache   = Cache::get($pendingAmountKey);
        $approvedCounterCache = Cache::get($approvedCounterKey);
        $approvedAmountCache  = Cache::get($approvedAmountKey);

        self::assertEquals(0, $draftCounterCache);
        self::assertEquals(0, $draftAmountCache);
        self::assertEquals(0, $pendingCounterCache);
        self::assertEquals(0, $pendingAmountCache);
        self::assertEquals(0, $approvedCounterCache);
        self::assertEquals(0, $approvedAmountCache);

        $this->countersDataProvider->recalculateCounters([$location->id]);

        $draftCounterCacheAfter    = Cache::get($draftCounterKey);
        $draftAmountCacheAfter     = Cache::get($draftAmountKey);
        $pendingCounterCacheAfter  = Cache::get($pendingCounterKey);
        $pendingAmountCacheAfter   = Cache::get($pendingAmountKey);
        $approvedCounterCacheAfter = Cache::get($approvedCounterKey);
        $approvedAmountCacheAfter  = Cache::get($approvedAmountKey);

        self::assertEquals($countOfDraftOrders, $draftCounterCacheAfter);
        self::assertGreaterThan($draftAmountCache, $draftAmountCacheAfter);
        self::assertEquals($countOfPendingOrders, $pendingCounterCacheAfter);
        self::assertGreaterThan($pendingAmountCache, $pendingAmountCacheAfter);
        self::assertEquals($countOfApprovedOrders, $approvedCounterCacheAfter);
        self::assertGreaterThan($approvedAmountCache, $approvedAmountCacheAfter);
    }
}
