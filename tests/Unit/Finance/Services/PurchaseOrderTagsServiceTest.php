<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderTag;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Tags\Models\Tag;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

/**
 * Class PurchaseOrderTagsServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrderTagsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(PurchaseOrderTagsServiceInterface::class);
        $models        = [
            PurchaseOrder::class,
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

    /**
     * @throws \Throwable
     */
    public function testAttachTag()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $this->service->attachTag($purchaseOrder->id, $tag->id);

        /** @var PurchaseOrderTag $purchaseOrderTag */
        $purchaseOrderTag = PurchaseOrderTag::query()
            ->where([
                'purchase_order_id' => $purchaseOrder->id,
                'tag_id'            => $tag->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $purchaseOrder->tags()->count());
        self::assertEquals($tag->id, $purchaseOrderTag->tag_id);
        self::assertEquals($purchaseOrder->id, $purchaseOrderTag->purchase_order_id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachTagThatAlreadyAttached()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        PurchaseOrderTag::insert([
            'purchase_order_id' => $purchaseOrder->id,
            'tag_id'            => $tag->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->attachTag($purchaseOrder->id, $tag->id);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachTag()
    {
        /** @var PurchaseOrderTag $purchaseOrderTag */
        $purchaseOrderTag = factory(PurchaseOrderTag::class)->create();

        $this->service->detachTag($purchaseOrderTag->purchase_order_id, $purchaseOrderTag->tag_id);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderTag::query()
            ->where([
                'purchase_order_id' => $purchaseOrderTag->purchase_order_id,
                'tag_id'            => $purchaseOrderTag->tag_id,
            ])
            ->firstOrFail();
    }
}
