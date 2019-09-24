<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderNote;
use App\Components\Notes\Models\Note;
use App\Http\Responses\Notes\FullNoteListResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrderNotesControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   api
 */
class PurchaseOrderNotesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.purchase_orders.view',
        'finance.purchase_orders.manage',
    ];

    public function testGetNotes()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrderNote::class, $count)->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $url = action('Finance\PurchaseOrderNotesController@getNotes', [
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data')
            ->assertValidSchema(FullNoteListResponse::class, true);
    }

    public function testAttachNoteToPurchaseOrder()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        $url  = action('Finance\PurchaseOrderNotesController@attachNote', [
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        PurchaseOrderNote::query()->where([
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ])->firstOrFail();
    }

    public function testFailToAttachNoteOwnedByOtherUser()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();
        $url  = action('Finance\PurchaseOrderNotesController@attachNote', [
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }

    public function testFailToAttachNoteWhenAlreadyAttached()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        factory(PurchaseOrderNote::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ]);
        $url = action('Finance\PurchaseOrderNotesController@attachNote', [
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('This note is already attached to specified purchase order.');
    }

    public function testDetachNoteFromPurchaseOrder()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        /** @var PurchaseOrderNote $purchaseOrderNote */
        $purchaseOrderNote = factory(PurchaseOrderNote::class)->create([
            'note_id' => $note->id,
        ]);

        $url = action('Finance\PurchaseOrderNotesController@detachNote', [
            'purchase_order_id' => $purchaseOrderNote->purchase_order_id,
            'note_id'           => $purchaseOrderNote->note_id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderNote::query()->where([
            'purchase_order_id' => $purchaseOrderNote->purchase_order_id,
            'note_id'           => $purchaseOrderNote->note_id,
        ])->firstOrFail();
    }

    public function testFailToDetachNoteOwnedByOtherUser()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create();
        /** @var PurchaseOrderNote $purchaseOrderNote */
        $purchaseOrderNote = factory(PurchaseOrderNote::class)->create([
            'note_id' => $note->id,
        ]);
        $url = action('Finance\PurchaseOrderNotesController@detachNote', [
            'purchase_order_id' => $purchaseOrderNote->purchase_order_id,
            'note_id'           => $purchaseOrderNote->note_id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }
}
