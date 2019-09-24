<?php

namespace Tests\API\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PurchaseOrderVirtualStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Http\Responses\Finance\PurchaseOrderInfoResponse;
use App\Http\Responses\Finance\PurchaseOrderListResponse;
use App\Models\User;
use Carbon\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrderListingControllerTest
 *
 * @package Tests\API\Finance
 * @group   purchase-orders
 * @group   finance
 * @group   finance-listings
 */
class PurchaseOrderListingControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.purchase_orders.view',
    ];

    public function setUp()
    {
        parent::setUp();

        $models       = [
            PurchaseOrder::class,
            AccountType::class,
            GLAccount::class,
            TaxRate::class,
            AccountingOrganization::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testGetInfoMethod()
    {
        $url = action('Finance\PurchaseOrderListingController@getInfo');

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(PurchaseOrderInfoResponse::class, true);
        $data     = $response->getData();

        self::assertArrayHasKey('draft', $data);
        self::assertArrayHasKey('pending_approval', $data);
        self::assertArrayHasKey('approved', $data);
    }

    public function testGetDraftMethod()
    {
        /** @var Location $location */
        $location      = factory(Location::class)->create();
        $countOfRecord = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfRecord)
            ->create([
                'location_id' => $location->id,
            ]);
        $url = action('Finance\PurchaseOrderListingController@getDraft', [
            'locations' => [$location->id],
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($countOfRecord)
            ->assertValidSchema(PurchaseOrderListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(PurchaseOrderVirtualStatuses::DRAFT, $item['virtual_status']);
        }
    }

    public function testGetDraftMethodWithFiltration()
    {
        /** @var Location $locationOne */
        $locationOne = factory(Location::class)->create();
        $this->user->locations()->attach($locationOne);
        $countForFirstLocation = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countForFirstLocation)->create([
            'location_id' => $locationOne->id,
        ]);

        /** @var Location $locationTwo */
        $locationTwo = factory(Location::class)->create();
        $this->user->locations()->attach($locationTwo);
        $countForSecondLocation = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countForSecondLocation)->create([
            'location_id' => $locationTwo->id,
        ]);

        $url      = action('Finance\PurchaseOrderListingController@getDraft', [
            'locations' => [$locationTwo->id],
        ]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($countForSecondLocation);

        foreach ($response->getData() as $purchaseOrder) {
            self::assertEquals($locationTwo->id, $purchaseOrder['location_id']);
        }
    }

    public function testGetDraftMethodShouldReturnValidationErrorResponse()
    {
        $params = [
            'locations' => 0,
        ];
        $url    = action('Finance\PurchaseOrderListingController@getDraft', $params);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testGetPendingApprovalMethod()
    {
        /** @var Location $location */
        $location      = factory(Location::class)->create();
        $countOfRecord = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfRecord)
            ->create([
                'location_id' => $location->id,
                'locked_at'   => Carbon::now(),
            ])
            ->each(function (PurchaseOrder $order) {
                $order->statuses()->create([
                    'status'  => FinancialEntityStatuses::DRAFT,
                    'user_id' => $this->user->id,
                ]);
                $order->approveRequests()->create([
                    'requester_id' => \Auth::id(),
                    'approver_id'  => factory(User::class)->create()->id,
                ]);
            });
        $url = action('Finance\PurchaseOrderListingController@getPendingApproval', [
            'locations' => [$location->id],
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRecord)
            ->assertValidSchema(PurchaseOrderListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(PurchaseOrderVirtualStatuses::PENDING_APPROVAL, $item['virtual_status']);
        }
    }

    public function testGetPendingApprovalMethodShouldReturnValidationErrorResponse()
    {
        $params = [
            'locations' => 0,
        ];
        $url    = action('Finance\PurchaseOrderListingController@getPendingApproval', $params);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testGetApprovedMethod()
    {
        /** @var Location $location */
        $location       = factory(Location::class)->create();
        $countOfRecord  = $this->faker->numberBetween(1, 5);
        $purchaseOrders = factory(PurchaseOrder::class, $countOfRecord)->create([
            'location_id' => $location->id,
            'locked_at'   => Carbon::now(),
        ]);
        foreach ($purchaseOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->statuses()->create([
                'user_id' => \Auth::id(),
                'status'  => FinancialEntityStatuses::APPROVED,
            ]);
        }
        $url = action('Finance\PurchaseOrderListingController@getApproved', [
            'locations' => [$location->id],
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRecord)
            ->assertValidSchema(PurchaseOrderListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(PurchaseOrderVirtualStatuses::APPROVED, $item['virtual_status']);
        }
    }

    public function testGetApprovedMethodShouldReturnValidationErrorResponse()
    {
        $params = [
            'locations' => 0,
        ];
        $url    = action('Finance\PurchaseOrderListingController@getApproved', $params);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testAllMethod()
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);
        $countOfApproved = $this->faker->numberBetween(1, 5);
        $purchaseOrders  = factory(PurchaseOrder::class, $countOfApproved)->create([
            'location_id' => $location->id,
            'locked_at'   => Carbon::now(),
        ]);
        foreach ($purchaseOrders as $purchaseOrder) {
            /** @var $purchaseOrder PurchaseOrder */
            $purchaseOrder->statuses()->create([
                'user_id' => \Auth::id(),
                'status'  => FinancialEntityStatuses::APPROVED,
            ]);
        }

        $countOfDraft = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfDraft)
            ->create([
                'location_id' => $location->id,
            ])
            ->each(function (PurchaseOrder $order) {
                $order->statuses()->create([
                    'status'  => FinancialEntityStatuses::DRAFT,
                    'user_id' => $this->user->id,
                ]);
            });

        $expectedCount = $countOfApproved + $countOfDraft;
        $url           = action('Finance\PurchaseOrderListingController@index');
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($expectedCount)
            ->assertValidSchema(PurchaseOrderListResponse::class, true);
    }
}
