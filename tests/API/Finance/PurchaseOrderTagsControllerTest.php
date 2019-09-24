<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderTag;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Tags\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrderTagsControllerTest
 *
 * @package Tests\API\Finance
 * @group   purchase-orders
 * @group   api
 */
class PurchaseOrderTagsControllerTest extends ApiTestCase
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

    public function testGetTags()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder  = factory(PurchaseOrder::class)->create();
        $countOfRecords = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrderTag::class, $countOfRecords)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);
        $url = action('Finance\PurchaseOrderTagsController@getTags', [
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($countOfRecords, 'data');
    }

    public function testAttachTag()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $url = action('Finance\PurchaseOrderTagsController@attachTag', [
            'purchase_order_id' => $purchaseOrder->id,
            'tag_id'            => $tag->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        PurchaseOrderTag::query()->where([
            'purchase_order_id' => $purchaseOrder->id,
            'tag_id'            => $tag->id,
        ])->firstOrFail();
    }

    public function testNotAllowedResponseWhenTagAlreadyAttached()
    {
        /** @var PurchaseOrderTag $purchaseOrderTag */
        $purchaseOrderTag = factory(PurchaseOrderTag::class)->create();
        $url              = action('Finance\PurchaseOrderTagsController@attachTag', [
            'purchase_order_id' => $purchaseOrderTag->purchase_order_id,
            'tag_id'            => $purchaseOrderTag->tag_id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testDetachTag()
    {
        /** @var PurchaseOrderTag $purchaseOrderTag */
        $purchaseOrderTag = factory(PurchaseOrderTag::class)->create();
        $url              = action('Finance\PurchaseOrderTagsController@detachTag', [
            'purchase_order_id' => $purchaseOrderTag->purchase_order_id,
            'tag_id'            => $purchaseOrderTag->tag_id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderTag::query()->where([
            'purchase_order_id' => $purchaseOrderTag->purchase_order_id,
            'tag_id'            => $purchaseOrderTag->tag_id,
        ])->firstOrFail();
    }
}
