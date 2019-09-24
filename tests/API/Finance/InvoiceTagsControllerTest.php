<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Tags\Models\Tag;
use Tests\API\ApiTestCase;

/**
 * Class InvoiceTagsControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 */
class InvoiceTagsControllerTest extends ApiTestCase
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
            Tag::class,
        ], $this->models);
    }

    public function testGetTagsMethod()
    {
        $countOfTags = $this->faker->numberBetween(2, 4);
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(Tag::class, $countOfTags)
            ->create()
            ->each(function (Tag $tag) use ($invoice) {
                $invoice->tags()->attach($tag);
            });

        $url = action('Finance\InvoiceTagsController@getTags', ['invoice_id' => $invoice->id]);
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfTags);
    }

    public function testGetTagsMethodShouldReturnNotFoundError()
    {
        $url = action('Finance\InvoiceTagsController@getTags', ['id' => 0]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testAttachTagMethod()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        $tag     = factory(Tag::class)->create();

        $url = action('Finance\InvoiceTagsController@attachTag', [
            'invoice_id' => $invoice->id,
            'tag_id'     => $tag->id,
        ]);
        $this->postJson($url)
            ->assertStatus(200);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertTrue($reloaded->tags->contains($tag));
    }

    public function testAttachTagMethodShouldReturnNotAllowedExceptionIfTagAlreadyAttached()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $invoice->tags()->attach($tag);

        $url = action('Finance\InvoiceTagsController@attachTag', [
            'invoice_id' => $invoice->id,
            'tag_id'     => $tag->id,
        ]);

        $this->postJson($url)
            ->assertNotAllowed();
    }

    public function testAttachTagMethodShouldReturnNotFoundErorForNonExistingTag()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        $url     = action('Finance\InvoiceTagsController@attachTag', [
            'invoice_id' => $invoice->id,
            'tag_id'     => 0,
        ]);

        $this->postJson($url)
            ->assertStatus(404);
    }

    public function testAttachTagMethodShouldReturnNotFoundErorForNonExistingInvoice()
    {
        $tag = factory(Tag::class)->create();
        $url = action('Finance\InvoiceTagsController@attachTag', [
            'invoice_id' => 0,
            'tag_id'     => $tag->id,
        ]);

        $this->postJson($url)
            ->assertStatus(404);
    }

    public function testDetachMethod()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $invoice->tags()->attach($tag);

        $url = action('Finance\InvoiceTagsController@detachTag', [
            'invoice_id' => $invoice->id,
            'tag_id'     => $tag->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertFalse($reloaded->tags->contains($tag));
    }
}
