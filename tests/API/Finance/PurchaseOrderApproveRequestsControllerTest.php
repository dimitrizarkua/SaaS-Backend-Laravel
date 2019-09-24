<?php

namespace Tests\API\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrderApproveRequestsControllerTest
 *
 * @package Tests\API\Finance
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrderApproveRequestsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.purchase_orders.view',
        'finance.purchase_orders.manage',
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

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetPurchaseOrderApproveRequests()
    {
        /** @var PurchaseOrder $model */
        $model           = factory(PurchaseOrder::class)->create();
        $countOfRequests = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrderApproveRequest::class, $countOfRequests)->create([
            'purchase_order_id' => $model->id,
        ]);

        $url = action('Finance\PurchaseOrderApproveRequestsController@getPurchaseOrderApproveRequests', [
            'id' => $model->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRequests);
    }

    public function testCreatePurchaseOrderApproveRequests()
    {
        $lockDay          = $this->faker->numberBetween(1, 15);
        $countOfApprovers = $this->faker->numberBetween(1, 5);
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        /** @var \Illuminate\Support\Collection $approvers */
        $approvers = factory(User::class, $countOfApprovers)
            ->create()
            ->each(function (User $user) use ($model) {
                $user->locations()->attach($model->location_id);
            });
        $request   = [
            'purchase_order_id' => $model->id,
            'requester_id'      => \Auth::id(),
            'approver_ids'      => $approvers->pluck('id')->toArray(),
        ];

        $url = action('Finance\PurchaseOrderApproveRequestsController@createPurchaseOrderApproveRequests', [
            'id' => $model->id,
        ]);
        $this->postJson($url, $request)
            ->assertStatus(200);

        $approveRequests = PurchaseOrderApproveRequest::all();
        self::assertCount($countOfApprovers, $approveRequests);
    }
}
