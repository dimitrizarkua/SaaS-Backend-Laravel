<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Resources\InvoiceItemResource;
use App\Components\Locations\Models\Location;
use Tests\API\ApiTestCase;

/**
 * Class InvoiceItemsControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 */
class InvoiceItemsControllerTest extends ApiTestCase
{
    public $permissions = [
        'finance.invoices.manage',
        'finance.invoices.view',
    ];

    /**
     * @var Invoice
     */
    private $invoice;
    /**
     * @var AccountingOrganization
     */
    private $accountingOrganization;
    /**
     * @var GLAccount
     */
    private $bankAccount;

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
        ], $this->models);

        $this->accountingOrganization = factory(AccountingOrganization::class)->create();
        $this->bankAccount            = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);

        $this->invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $this->invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
    }

    public function testCreateInvoiceItem()
    {
        $data = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->word,
            'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
            'quantity'      => $this->faker->numberBetween(1, 4),
            'discount'      => $this->faker->randomFloat(2, 10, 50),
            'gl_account_id' => $this->bankAccount->id,
            'tax_rate_id'   => $this->bankAccount->tax_rate_id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\InvoiceItemsController@store', [
            'invoice' => $this->invoice->id,
        ]);

        $this->postJson($url, $data)
            ->assertStatus(201)
            ->assertValidSchema(InvoiceItemResource::class);

        $model = Invoice::findOrFail($this->invoice->id);
        self::assertCount(1, $model->items);

        /** @var InvoiceItem $item */
        $item = $model->items->where('description')->first();
        self::assertNotNull($item);
        self::assertEquals($data['gs_code_id'], $item->gs_code_id);
        self::assertEquals($data['unit_cost'], $item->unit_cost);
        self::assertEquals($data['quantity'], $item->quantity);
        self::assertEquals($data['discount'], $item->discount);
        self::assertEquals($data['gl_account_id'], $item->gl_account_id);
        self::assertEquals($data['tax_rate_id'], $item->tax_rate_id);
        self::assertEquals($data['position'], $item->position);
    }

    public function testCreateInvoiceItemWithNullDiscount()
    {
        $data = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->word,
            'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
            'quantity'      => $this->faker->numberBetween(1, 4),
            'discount'      => null,
            'gl_account_id' => $this->bankAccount->id,
            'tax_rate_id'   => $this->bankAccount->tax_rate_id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\InvoiceItemsController@store', [
            'invoice' => $this->invoice->id,
        ]);

        $this->postJson($url, $data)
            ->assertStatus(201);

        $model = Invoice::findOrFail($this->invoice->id);
        self::assertCount(1, $model->items);

        /** @var InvoiceItem $item */
        $item = $model->items->where('description')->first();
        self::assertNotNull($item);
        self::assertEquals(0, $item->discount);
    }

    public function testCreateItemMethodForNonDraftInvoiceShouldReturnError()
    {
        $this->invoice->lockUp();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $this->invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $data = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'description'   => $this->faker->word,
            'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
            'quantity'      => $this->faker->numberBetween(1, 4),
            'discount'      => $this->faker->randomFloat(2, 10, 50),
            'gl_account_id' => $this->bankAccount->id,
            'tax_rate_id'   => $this->bankAccount->tax_rate_id,
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\InvoiceItemsController@store', [
            'invoice' => $this->invoice->id,
        ]);

        $this->postJson($url, $data)
            ->assertStatus(405);

        self::assertCount(0, $this->invoice->items);
    }

    public function testUpdateInvoiceItem()
    {
        $item = factory(InvoiceItem::class)->create([
            'invoice_id' => $this->invoice->id,
        ]);

        $data = [
            'description' => $this->faker->word,
            'quantity'    => $this->faker->numberBetween(1, 4),
            'position'    => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\InvoiceItemsController@update', [
            'invoice'      => $this->invoice->id,
            'invoice_item' => $item->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(200)
            ->assertValidSchema(InvoiceItemResource::class);

        $model = InvoiceItem::findOrFail($item->id);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['quantity'], $model->quantity);
        self::assertEquals($data['position'], $model->position);
    }

    public function testUpdateItemMethodForNonDraftInvoiceShouldReturnError()
    {
        $this->invoice->lockUp();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $this->invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $item = factory(InvoiceItem::class)->create([
            'invoice_id' => $this->invoice->id,
        ]);
        $data = [
            'description' => $this->faker->word,
            'quantity'    => $this->faker->numberBetween(1, 4),
            'position'    => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\InvoiceItemsController@update', [
            'invoice'      => $this->invoice->id,
            'invoice_item' => $item->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(405);
    }

    public function testDeleteMethod()
    {
        $item = factory(InvoiceItem::class)->create([
            'invoice_id' => $this->invoice->id,
        ]);

        $invoiceItemId = $item->id;
        $url           = action('Finance\InvoiceItemsController@destroy', [
            'invoice'      => $this->invoice->id,
            'invoice_item' => $invoiceItemId,
        ]);

        self::assertCount(1, $this->invoice->items);
        $this->deleteJson($url)
            ->assertStatus(200);
        $model = Invoice::findOrFail($this->invoice->id);
        self::assertCount(0, $model->items);
        $invoiceItem = InvoiceItem::find($invoiceItemId);
        self::assertNull($invoiceItem);
    }

    public function testDeleteItemMethodForNonDraftInvoiceShouldReturnError()
    {
        $this->invoice->lockUp();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $this->invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $item = factory(InvoiceItem::class)->create([
            'invoice_id' => $this->invoice->id,
        ]);

        $url = action('Finance\InvoiceItemsController@destroy', [
            'invoice'      => $this->invoice->id,
            'invoice_item' => $item->id,
        ]);
        $this->deleteJson($url)
            ->assertStatus(405);
    }
}
