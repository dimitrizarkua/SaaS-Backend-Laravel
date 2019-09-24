<?php

namespace Tests\API\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Resources\PurchaseOrderItemResource;
use App\Components\Finance\Resources\PurchaseOrderListResource;
use App\Components\Locations\Models\Location;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrderItemsControllerTest
 *
 * @package Tests\API\Finance
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrderItemsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.purchase_orders.view',
        'finance.purchase_orders.manage',
    ];

    public function setUp()
    {
        parent::setUp();

        $models       = [
            PurchaseOrderItem::class,
            GSCode::class,
            PurchaseOrder::class,
            AccountType::class,
            GLAccount::class,
            TaxRate::class,
            AccountingOrganization::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder  = factory(PurchaseOrder::class)->create();
        $countOfRecords = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrderItem::class, $countOfRecords)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        $url = action('Finance\PurchaseOrderItemsController@store', [
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRecords);
    }

    public function testCreateMethod()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $request = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->sentence,
            'unit_cost'     => $this->faker->randomFloat(2, 1, 1000),
            'quantity'      => $this->faker->numberBetween(1, 10),
            'markup'        => $this->faker->numberBetween(1, 100),
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];
        $url     = action('Finance\PurchaseOrderItemsController@store', [
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertValidSchema(PurchaseOrderItemResource::class);

        $data  = $response->getData();
        $model = PurchaseOrderItem::findOrFail($data['id']);

        self::assertEquals($purchaseOrder->id, $model->purchase_order_id);
        self::assertEquals($data['gs_code_id'], $model->gs_code_id);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['unit_cost'], $model->unit_cost);
        self::assertEquals($data['quantity'], $model->quantity);
        self::assertEquals($data['markup'], $model->markup);
        self::assertEquals($data['gl_account_id'], $model->gl_account_id);
        self::assertEquals($data['tax_rate_id'], $model->tax_rate_id);
        self::assertEquals($data['position'], $model->position);
    }

    public function testShouldBeValidationErrorResponse()
    {
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create();
        $url   = action('Finance\PurchaseOrderItemsController@store', [
            'purchase_order_id' => $model->id,
        ]);

        $this->postJson($url, [])
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var PurchaseOrderItem $model */
        $model = factory(PurchaseOrderItem::class)->create();
        $url   = action('Finance\PurchaseOrderItemsController@show', [
            'purchase_order_id'      => $model->purchase_order_id,
            'purchase_order_item_id' => $model->id,
        ]);

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertValidSchema(PurchaseOrderItemResource::class);

        $data = $response->getData();

        self::assertEquals($data['id'], $model->id);
        self::assertEquals($data['purchase_order_id'], $model->purchase_order_id);
        self::assertEquals($data['gs_code_id'], $model->gs_code_id);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['unit_cost'], $model->unit_cost);
        self::assertEquals($data['quantity'], $model->quantity);
        self::assertEquals($data['markup'], $model->markup);
        self::assertEquals($data['gl_account_id'], $model->gl_account_id);
        self::assertEquals($data['tax_rate_id'], $model->tax_rate_id);
        self::assertEquals($data['position'], $model->position);
    }

    public function testShowMethodShouldReturnNotFound()
    {
        /** @var PurchaseOrderItem $model */
        $model = factory(PurchaseOrderItem::class)->create();
        $url   = action('Finance\PurchaseOrderItemsController@show', [
            'purchase_order_id'      => $model->id,
            'purchase_order_item_id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdateMethod()
    {
        /** @var PurchaseOrderItem $model */
        $model = factory(PurchaseOrderItem::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $url     = action('Finance\PurchaseOrderItemsController@update', [
            'purchase_order_id'      => $model->purchase_order_id,
            'purchase_order_item_id' => $model->id,
        ]);
        $request = [
            'description' => $this->faker->sentence,
            'unit_cost'   => $this->faker->randomFloat(2, 1, 1000),
            'quantity'    => $this->faker->numberBetween(1, 10),
            'markup'      => $this->faker->numberBetween(1, 100),
            'position'    => $this->faker->numberBetween(1, 10),
        ];

        $this->patchJson($url, $request)
            ->assertStatus(200);
        $reloaded = PurchaseOrderItem::findOrFail($model->id);

        self::assertEquals($request['description'], $reloaded->description);
        self::assertEquals($request['unit_cost'], $reloaded->unit_cost);
        self::assertEquals($request['quantity'], $reloaded->quantity);
        self::assertEquals($request['markup'], $reloaded->markup);
        self::assertEquals($request['position'], $reloaded->position);
    }

    public function testUpdateMethodShouldReturnValidationError()
    {
        /** @var PurchaseOrderItem $model */
        $model   = factory(PurchaseOrderItem::class)->create();
        $url     = action('Finance\PurchaseOrderItemsController@update', [
            'purchase_order_id'      => $model->purchase_order_id,
            'purchase_order_item_id' => $model->id,
        ]);
        $request = [
            'unit_cost' => null,
            'quantity'  => null,
            'markup'    => null,
        ];

        $this->patchJson($url, $request)
            ->assertStatus(422);
    }

    public function testDestroyMethod()
    {
        /** @var PurchaseOrderItem $model */
        $model = factory(PurchaseOrderItem::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $url = action('Finance\PurchaseOrderItemsController@destroy', [
            'purchase_order_id'      => $model->purchase_order_id,
            'purchase_order_item_id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderItem::findOrFail($model->id);
    }
}
