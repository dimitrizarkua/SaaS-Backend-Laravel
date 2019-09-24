<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PurchaseOrderItemsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Exceptions\Api\ValidationException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\Finance\PurchaseOrdersTestFactory;

/**
 * Class PurchaseOrderItemsServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrderItemsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderItemsServiceInterface
     */
    private $service;

    /**
     * @var float
     */
    private $defaultUnitCost;

    /**
     * @var float
     */
    private $defaultQuantity;

    /**
     * @var float
     */
    private $defaultMarkUp;

    public function setUp()
    {
        parent::setUp();

        $this->defaultUnitCost = $this->faker->randomFloat(2, 1, 1000);
        $this->defaultQuantity = $this->faker->numberBetween(1, 10);
        $this->defaultMarkUp   = $this->faker->numberBetween(1, 100);
        $this->service         = $this->app->make(PurchaseOrderItemsServiceInterface::class);
        $models                = [
            PurchaseOrderItem::class,
            GSCode::class,
            PurchaseOrder::class,
            AccountType::class,
            GLAccount::class,
            TaxRate::class,
            AccountingOrganization::class,
            Location::class,
        ];
        $this->models          = array_merge($models, $this->models);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testGetPurchaseOrderItem()
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();

        $reloaded = $this->service->getPurchaseOrderItem($purchaseOrderItem->purchase_order_id, $purchaseOrderItem->id);

        self::assertEquals($purchaseOrderItem->purchase_order_id, $reloaded->purchase_order_id);
        self::assertEquals($purchaseOrderItem->gs_code_id, $reloaded->gs_code_id);
        self::assertEquals($purchaseOrderItem->description, $reloaded->description);
        self::assertEquals($purchaseOrderItem->unit_cost, $reloaded->unit_cost);
        self::assertEquals($purchaseOrderItem->quantity, $reloaded->quantity);
        self::assertEquals($purchaseOrderItem->markup, $reloaded->markup);
        self::assertEquals($purchaseOrderItem->gl_account_id, $reloaded->gl_account_id);
        self::assertEquals($purchaseOrderItem->tax_rate_id, $reloaded->tax_rate_id);
        self::assertEquals($purchaseOrderItem->position, $reloaded->position);
    }

    public function testFailToGetPurchaseOrderItemWhenIncorrectPurchaseOrderId()
    {
        $purchaseOrder = PurchaseOrdersTestFactory::createDraft();
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();

        self::expectException(ModelNotFoundException::class);
        $this->service->getPurchaseOrderItem($purchaseOrder->id, $purchaseOrderItem->id);
    }

    public function testCreatePurchaseOrderItem()
    {
        $purchaseOrder = PurchaseOrdersTestFactory::createDraft();
        $attributes    = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->sentence,
            'unit_cost'     => $this->defaultUnitCost,
            'quantity'      => $this->defaultQuantity,
            'markup'        => $this->defaultMarkUp,
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $created = $this->service->createPurchaseOrderItem($purchaseOrder->id, $attributes);

        self::assertEquals($purchaseOrder->id, $created->purchase_order_id);
        self::assertEquals($attributes['gs_code_id'], $created->gs_code_id);
        self::assertEquals($attributes['description'], $created->description);
        self::assertEquals($attributes['unit_cost'], $created->unit_cost);
        self::assertEquals($attributes['quantity'], $created->quantity);
        self::assertEquals($attributes['markup'], $created->markup);
        self::assertEquals($attributes['gl_account_id'], $created->gl_account_id);
        self::assertEquals($attributes['tax_rate_id'], $created->tax_rate_id);
        self::assertEquals($attributes['position'], $created->position);
    }

    public function testFailToCreatePurchaseOrderItemWhenValidationError()
    {
        $purchaseOrder = PurchaseOrdersTestFactory::createDraft();

        self::expectException(ValidationException::class);
        $this->service->createPurchaseOrderItem($purchaseOrder->id, []);
    }

    public function testFailToCreatePurchaseOrderItemWhenPurchaseOrderIsApproved()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => $this->faker->dateTime(),
        ]);
        $attributes    = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->sentence,
            'unit_cost'     => $this->defaultUnitCost,
            'quantity'      => $this->defaultQuantity,
            'markup'        => $this->defaultMarkUp,
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];
        /** @var User $user */
        $user = factory(User::class)->create();
        PurchaseOrderStatus::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $user->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->createPurchaseOrderItem($purchaseOrder->id, $attributes);
    }

    public function testUpdatePurchaseOrderItem()
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrderItem->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $attributes = [
            'unit_cost' => $this->defaultUnitCost,
            'quantity'  => $this->defaultQuantity,
            'markup'    => $this->defaultMarkUp,
            'position'  => $this->faker->numberBetween(1, 10),
        ];

        $this->service->updatePurchaseOrderItem(
            $purchaseOrderItem->purchase_order_id,
            $purchaseOrderItem->id,
            $attributes
        );

        /** @var PurchaseOrderItem $purchaseOrderItem */
        $reloaded = PurchaseOrderItem::findOrFail($purchaseOrderItem->id);

        self::assertEquals($attributes['unit_cost'], $reloaded->unit_cost);
        self::assertEquals($attributes['quantity'], $reloaded->quantity);
        self::assertEquals($attributes['markup'], $reloaded->markup);
        self::assertEquals($attributes['position'], $reloaded->position);
    }

    public function testFailToUpdatePurchaseOrderItemWhenIncorrectPurchaseOrderId()
    {
        $purchaseOrder = PurchaseOrdersTestFactory::createDraft();
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();
        $attributes        = [
            'unit_cost' => $this->defaultUnitCost,
            'quantity'  => $this->defaultQuantity,
            'markup'    => $this->defaultMarkUp,
        ];

        self::expectException(ModelNotFoundException::class);
        $this->service->updatePurchaseOrderItem($purchaseOrder->id, $purchaseOrderItem->id, $attributes);
    }

    public function testFailUpdatePurchaseOrderItemWhenValidationError()
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrderItem->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $attributes = [
            'gs_code_id' => null,
        ];

        self::expectException(ValidationException::class);
        $this->service->updatePurchaseOrderItem(
            $purchaseOrderItem->purchase_order_id,
            $purchaseOrderItem->id,
            $attributes
        );
    }

    public function testFailToUpdatePurchaseOrderItemWhenPurchaseOrderIsApproved()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => $this->faker->dateTime(),
        ]);
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        $attributes        = [
            'unit_cost' => $this->defaultUnitCost,
            'quantity'  => $this->defaultQuantity,
            'markup'    => $this->defaultMarkUp,
        ];
        /** @var User $user */
        $user = factory(User::class)->create();
        PurchaseOrderStatus::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $user->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->updatePurchaseOrderItem($purchaseOrder->id, $purchaseOrderItem->id, $attributes);
    }

    public function testDeletePurchaseOrderItem()
    {
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrderItem->purchase_order_id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        $this->service->deletePurchaseOrderItem($purchaseOrderItem->purchase_order_id, $purchaseOrderItem->id);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderItem::whereId($purchaseOrderItem->id)
            ->firstOrFail();
    }

    public function testFailToDeletePurchaseOrderItemWhenIncorrectPurchaseOrderId()
    {
        $purchaseOrder = PurchaseOrdersTestFactory::createDraft();
        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create();

        self::expectException(ModelNotFoundException::class);
        $this->service->deletePurchaseOrderItem($purchaseOrder->id, $purchaseOrderItem->id);
    }

    public function testFailToDeletePurchaseOrderItemWhenPurchaseOrderIsApproved()
    {
        $purchaseOrder = factory(PurchaseOrder::class)->create([
            'locked_at' => $this->faker->dateTime(),
        ]);

        /** @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        /** @var User $user */
        $user = factory(User::class)->create();
        PurchaseOrderStatus::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id'           => $user->id,
            'status'            => FinancialEntityStatuses::APPROVED,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->deletePurchaseOrderItem($purchaseOrder->id, $purchaseOrderItem->id);
    }
}
