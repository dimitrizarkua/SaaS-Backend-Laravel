<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Events\NoteAttachedToInvoice;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use App\Http\Responses\Notes\FullNoteListResponse;
use Tests\API\ApiTestCase;

/**
 * Class InvoiceNotesControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 */
class InvoiceNotesControllerTest extends ApiTestCase
{
    public $permissions = [
        'finance.invoices.view',
        'finance.invoices.manage',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
            Location::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Contact::class,
            InvoiceItem::class,
            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
            Note::class,
        ], $this->models);
    }

    public function testGetNotesMethod()
    {
        $countOfNotes = $this->faker->numberBetween(2, 4);
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(Note::class, $countOfNotes)
            ->create()
            ->each(function (Note $note) use ($invoice) {
                $invoice->notes()->attach($note);
            });

        $url = action('Finance\InvoiceNotesController@getNotes', ['invoice_id' => $invoice->id]);
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfNotes)
            ->assertValidSchema(FullNoteListResponse::class, true);
    }

    public function testGetNOteMethodShouldReturnNotFoundError()
    {
        $url = action('Finance\InvoiceNotesController@getNotes', ['id' => 0]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    /**
     * @throws \Exception
     */
    public function testAttachNoteMethod()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        $note    = factory(Note::class)->create();

        $url = action('Finance\InvoiceNotesController@attachNote', [
            'invoice_id' => $invoice->id,
            'note_id'    => $note->id,
        ]);
        $this->expectsEvents(NoteAttachedToInvoice::class);
        $this->postJson($url)
            ->assertStatus(200);

        $reloaded = Invoice::findOrFail($invoice->id);

        self::assertTrue($reloaded->notes->contains($note));
    }

    public function testAttachNoteMethodShouldReturnNotAllowedExceptionIfNoteAlreadyAttached()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();
        $invoice->notes()->attach($note);

        $url = action('Finance\InvoiceNotesController@attachNote', [
            'invoice_id' => $invoice->id,
            'note_id'    => $note->id,
        ]);

        $this->postJson($url)
            ->assertNotAllowed();
    }

    public function testAttachNoteMethodShouldReturnNotFoundErorForNonExistingNote()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        $url     = action('Finance\InvoiceNotesController@attachNote', [
            'invoice_id' => $invoice->id,
            'note_id'    => 0,
        ]);

        $this->postJson($url)
            ->assertStatus(404);
    }

    public function testAttachNoteMethodShouldReturnNotFoundErorForNonExistingInvoice()
    {
        $note = factory(Note::class)->create();
        $url  = action('Finance\InvoiceNotesController@attachNote', [
            'invoice_id' => 0,
            'note_id'    => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(404);
    }

    public function testDetachMethod()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();
        $invoice->notes()->attach($note);

        $url = action('Finance\InvoiceNotesController@detachNote', [
            'invoice_id' => $invoice->id,
            'note_id'    => $note->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertFalse($reloaded->notes->contains($note));
    }
}
