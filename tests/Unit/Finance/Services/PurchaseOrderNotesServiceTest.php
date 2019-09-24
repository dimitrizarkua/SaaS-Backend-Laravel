<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderNote;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class PurchaseOrderNotesServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrderNotesServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(PurchaseOrderNotesServiceInterface::class);
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

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testAttachNote()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();

        $this->service->attachNote($purchaseOrder->id, $note->id);

        PurchaseOrderNote::query()
            ->where([
                'note_id'           => $note->id,
                'purchase_order_id' => $purchaseOrder->id,
            ])
            ->firstOrFail();
        self::assertEquals(1, $purchaseOrder->notes()->count());
    }

    public function testFailToAttachNoteWasAlreadyAttached()
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();

        PurchaseOrderNote::insert([
            'purchase_order_id' => $purchaseOrder->id,
            'note_id'           => $note->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->attachNote($purchaseOrder->id, $note->id);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachNote()
    {
        /** @var PurchaseOrderNote $purchaseOrderNote */
        $purchaseOrderNote = factory(PurchaseOrderNote::class)->create();

        $this->service->detachNote($purchaseOrderNote->purchase_order_id, $purchaseOrderNote->note_id);

        self::expectException(ModelNotFoundException::class);
        PurchaseOrderNote::query()->where([
            'purchase_order_id' => $purchaseOrderNote->purchase_order_id,
            'note_id'           => $purchaseOrderNote->note_id,
        ])->firstOrFail();
    }
}
