<?php

namespace Tests\API\Finance;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Enums\TaxRates;
use App\Components\Finance\Events\CreditCardPaymentProcessedEvent;
use App\Components\Finance\Events\InvoiceApproved;
use App\Components\Finance\Events\InvoiceCreated;
use App\Components\Finance\Events\InvoiceDeleted;
use App\Components\Finance\Events\InvoiceUpdated;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\CreditCardTransaction;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Resources\InvoiceResource;
use App\Components\Finance\Services\InvoicesService;
use App\Components\Locations\Models\Location;
use App\Helpers\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class InvoicesControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 */
class InvoicesControllerTest extends ApiTestCase
{
    public const TEST_CARD_NUMBER_SUCCESS            = '4200000000000000';
    public const TEST_CARD_NUMBER_DECLINED           = '4100000000000001';
    public const TEST_CARD_NUMBER_INSUFFICIENT_FUNDS = '4000000000000002';
    public const TEST_CARD_NUMBER_INVALID_CVV        = '4900000000000003';
    public const TEST_CARD_NUMBER_INVALID_CARD       = '4800000000000004';
    public const TEST_CARD_NUMBER_PROCESSING_ERROR   = '4700000000000005';
    public const TEST_CARD_NUMBER_SUSPECTED_FRAUD    = '4600000000000006';
    public const TEST_CARD_NUMBER_UNKNOWN            = '4400000000000099';

    public const TEST_API_SECRET_KEY = 'tuad0-6maWzAcDmmbzP6Nw';

    public $permissions = [
        'finance.invoices.manage',
        'finance.invoices.view',
        'finance.payments.receive',
    ];

    public function setUp(): void
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
            ContactCompanyProfile::class,
            CreditCardTransaction::class,
        ], $this->models);

        Event::fake();
    }

    /**
     * @throws \Throwable
     */
    public function testCreateInvoice(): void
    {
        $location = factory(Location::class)->create();

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);

        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'contact_id'        => $contact->id,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $accountingOrganization->payment_details_account_id = $paymentDetailsAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Address $address */
        $address          = factory(Address::class)->create();
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);
        /** @var GLAccount $bankAccount */
        $bankAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);

        $this->user->locations()->attach($location);

        $data = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => null,
            'document_id'          => null,
            'due_at'               => Carbon::now()->addDays(4),
            'reference'            => $this->faker->word,
            'date'                 => Carbon::now()->format('Y-m-d'),
            'items'                => [
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'discount'      => $this->faker->randomFloat(2, 1, 100),
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                    'position'      => $this->faker->numberBetween(1, 10),
                ],
            ],
        ];

        $this->expectsEvents(InvoiceCreated::class);
        $url      = action('Finance\InvoicesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $invoiceId = $response->getData('id');
        $model     = Invoice::find($invoiceId);
        self::assertNotNull($model);

        self::assertNotNull($model);
        self::assertEquals($data['location_id'], $model->location_id);
        self::assertEquals($accountingOrganization->id, $model->accounting_organization_id);
        self::assertEquals($address->full_address, $model->recipient_address);
        self::assertEquals($recipientContact->getContactName(), $model->recipient_name);
        self::assertEquals($data['recipient_contact_id'], $model->recipient_contact_id);
        self::assertEquals($data['reference'], $model->reference);
        self::assertEquals(FinancialEntityStatuses::DRAFT, $model->latestStatus->status);
        self::assertCount(1, $model->items);
    }

    /**
     * @throws \Throwable
     */
    public function testInvoiceShouldBeCreatedWithNullDiscountForItem(): void
    {
        $location = factory(Location::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);

        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'contact_id'        => $contact->id,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $accountingOrganization->payment_details_account_id = $paymentDetailsAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Address $address */
        $address          = factory(Address::class)->create();
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);
        /** @var GLAccount $bankAccount */
        $bankAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);

        $this->user->locations()->attach($location);

        $data = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => null,
            'document_id'          => null,
            'due_at'               => Carbon::now()->addDays(4),
            'reference'            => $this->faker->word,
            'date'                 => Carbon::now()->format('Y-m-d'),
            'items'                => [
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'discount'      => null,
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                    'position'      => $this->faker->numberBetween(1, 10),
                ],
            ],
        ];

        $url      = action('Finance\InvoicesController@store');
        $response = $this->postJson($url, $data)
            ->assertStatus(201);

        $invoiceId = $response->getData('id');
        $model     = Invoice::find($invoiceId);
        self::assertEquals(0, $model->items->first()->discount);
    }

    public function testCreateInvoiceShouldReturnValidationErrorResponse(): void
    {
        $url = action('Finance\InvoicesController@store');
        $this->postJson($url)
            ->assertStatus(422);
    }

    public function testShowInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        $address = factory(Address::class)->create();
        factory(InvoiceItem::class, 2)->create([
            'invoice_id' => $invoice->id,
        ]);
        Contact::findOrFail($invoice->recipient_contact_id)
            ->addresses()
            ->attach($address, ['type' => AddressContactTypes::MAILING]);

        $url = action('Finance\InvoicesController@show', ['id' => $invoice->id]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(InvoiceResource::class);

        $data = $response->getData();
        self::assertEquals($invoice->id, $data['id']);
    }

    public function testShowInvoiceShouldReturnNotFoundResponse(): void
    {
        $url = action('Finance\InvoicesController@show', ['id' => 0]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        $url   = action('Finance\InvoicesController@update', ['id' => $invoice->id]);
        $dueAt = Carbon::now()->addDay();
        $data  = [
            'recipient_name'    => $this->faker->word,
            'recipient_address' => $this->faker->address,
            'due_at'            => $dueAt,
            'date'              => Carbon::now()->addDay()->format('Y-m-d'),
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);
        Event::dispatched(InvoiceUpdated::class);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertEquals($data['recipient_name'], $reloaded->recipient_name);
        self::assertEquals($data['recipient_address'], $reloaded->recipient_address);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateInvoiceReference(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        $url  = action('Finance\InvoicesController@update', ['id' => $invoice->id]);
        $data = [
            'reference' => $this->faker->word,
        ];

        $this->patchJson($url, $data)
            ->assertStatus(200);
        Event::dispatched(InvoiceUpdated::class);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertEquals($data['reference'], $reloaded->reference);
    }

    public function testUpdateInvoiceShouldReturnValidationError(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        $url  = action('Finance\InvoicesController@update', ['id' => $invoice->id]);
        $data = [
            'recipient_contact_id' => 0,
        ];

        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testUpdateInvoiceShouldReturnForbiddenResponse(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'locked_at' => Carbon::now(),
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $url  = action('Finance\InvoicesController@update', ['id' => $invoice->id]);
        $data = [
            'recipient_name'    => $this->faker->word,
            'recipient_address' => $this->faker->address,
        ];
        $this->patchJson($url, $data)
            ->assertStatus(403);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        $invoiceId = $invoice->id;
        $url       = action('Finance\InvoicesController@destroy', ['id' => $invoiceId]);
        $this->expectsEvents(InvoiceDeleted::class);
        $this->delete($url)
            ->assertStatus(200);

        $model = Invoice::find($invoiceId);
        self::assertNull($model);
    }

    public function testDeleteInvoiceShouldReturnNotAllowedError(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $url = action('Finance\InvoicesController@destroy', ['id' => $invoice->id]);

        $this->deleteJson($url)
            ->assertNotAllowed('You can\'t delete the invoice because it has already been approved.');
    }

    /**
     * @throws \Throwable
     */
    public function testApproveInvoice(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDays(2)->day,
        ]);
        /** @var GLAccount $receivableAccount */
        $receivableAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                //Asset type (Increasing is debit)
                'increase_action_is_debit' => true,
            ]),
        ]);
        /** @var GLAccount $salesAccount */
        $salesAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'increase_action_is_debit' => false,
            ]),
        ]);

        $taxPayableAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'increase_action_is_debit' => false,
            ]),
        ]);

        $accountingOrganization->tax_payable_account_id         = $taxPayableAccount->id;
        $accountingOrganization->accounts_receivable_account_id = $receivableAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        $amount = $this->faker->randomFloat(2, 100, 1000);

        $taxRate = 0.1;
        /** @var InvoiceItem $invoiceItem */
        factory(InvoiceItem::class)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amount,
            'quantity'      => 1,
            'discount'      => 0,
            'gl_account_id' => $salesAccount->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        $this->user->invoice_approve_limit = $invoice->getTotalAmount();
        $this->user->saveOrFail();
        $this->user->locations()->attach($invoice->location_id);

        //Create approve request
        $countOfRequests = $this->faker->numberBetween(2, 4);
        factory(InvoiceApproveRequest::class, $countOfRequests)
            ->create([
                'invoice_id' => $invoice->id,
            ]);
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id'  => $invoice->id,
            'approver_id' => $this->user->id,
        ]);

        $this->expectsEvents(InvoiceApproved::class);
        $url = action('Finance\InvoicesController@approve', ['invoice_id' => $invoice->id]);
        $this->postJson($url)
            ->assertStatus(200);

        $approveRequestList = InvoiceApproveRequest::query()
            ->where('invoice_id', $invoice->id)
            ->get();

        self::assertCount(1, $approveRequestList);
        $approveRequest = $approveRequestList->first();
        self::assertNotNull($approveRequest->approved_at);
    }

    public function testApproveInvoiceShouldReturnNotAllowedErrorResponse(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => 'NON_EXISTING_STATUS',
        ]);

        $url = action('Finance\InvoicesController@approve', ['invoice_id' => $invoice->id]);
        $this->postJson($url)
            ->assertNotAllowed('Unable to change invoice status.');
    }

    /**
     * @throws \Exception
     */
    public function testReceiveCreditCardPaymentSuccess(): void
    {
        $location = factory(Location::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);

        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month'   => Carbon::now()->addDay()->day,
            'contact_id'          => $contact->id,
            'is_active'           => true,
            'cc_payments_api_key' => self::TEST_API_SECRET_KEY,
        ]);
        $accountingOrganization->locations()->attach($location);

        $initSourceBalance = 100.00;

        /** Clearing account */
        GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganization->id,
            $increaseActionIsDebit = true,
            $initSourceBalance,
            $isBankAccount = false,
            GLAccount::CLEARING_ACCOUNT_CODE
        );

        /** Bank account */
        GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganization->id,
            $increaseActionIsDebit = true,
            $initSourceBalance = 0,
            $isBankAccount = true
        );

        $invoiceCount       = 1;
        $invoiceData        = [
            'accounting_organization_id' => $accountingOrganization->id,
        ];
        $withApproveRequest = true;

        $invoice = InvoicesTestFactory::createInvoices(
            $invoiceCount,
            $invoiceData,
            FinancialEntityStatuses::APPROVED,
            $withApproveRequest
        );

        $cost = 100.12;
        factory(InvoiceItem::class)->create([
            'invoice_id'  => $invoice->first()->id,
            'unit_cost'   => $cost,
            'quantity'    => 1,
            'discount'    => 0,
            'tax_rate_id' => factory(TaxRate::class)->create(['rate' => 0])->id,
        ]);

        $data = [
            'name'             => $this->faker->name,
            'number'           => self::TEST_CARD_NUMBER_SUCCESS,
            'cvv'              => $this->faker->numberBetween(100, 999),
            'email'            => $this->faker->email,
            'billing_address1' => $this->faker->address,
            'billing_city'     => $this->faker->city,
            'billing_country'  => $this->faker->country,
            'expiry_month'     => Carbon::now()->addMonth()->month,
            'expiry_year'      => Carbon::now()->addYear()->year,
        ];

        $invoiceId = $invoice->first()->id;
        $url       = action('Finance\InvoicesController@receiveCreditCardPayment', ['invoice' => $invoiceId]);

        $response = $this->postJson($url, $data);
        $response->assertStatus(200)
            ->assertSeeData();

        Event::assertDispatched(
            CreditCardPaymentProcessedEvent::class,
            function (CreditCardPaymentProcessedEvent $event) use ($data) {
                return $event->recipientEmail === $data['email'];
            }
        );

        $data = $response->getData();

        $invoicePayment = InvoicePayment::query()
            ->where([
                'payment_id' => $data['id'],
                'invoice_id' => $invoiceId,
            ])->firstOrFail();

        $creditCardTransaction = CreditCardTransaction::query()
            ->where([
                'payment_id' => $data['id'],
            ])->firstOrFail();

        $payment = Payment::findOrFail($data['id']);

        self::assertEquals(bccomp($payment->amount, $data['amount'], 2), 0);
        self::assertEquals($creditCardTransaction->payment_id, $payment->id);
        self::assertEquals(bccomp($cost, $data['amount'], 2), 0);
        self::assertEquals(PaymentTypes::CREDIT_CARD, $data['type']);
        self::assertEquals(bccomp($invoicePayment->amount, $data['amount'], 2), 0);
        self::assertEquals(bccomp($creditCardTransaction->amount, $data['amount'], 2), 0);
    }

    /**
     * @throws \Exception
     */
    public function testFailToReceiveCreditCardPaymentInsufficientFunds(): void
    {
        $location = factory(Location::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);

        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month'   => Carbon::now()->addDay()->day,
            'contact_id'          => $contact->id,
            'is_active'           => true,
            'cc_payments_api_key' => self::TEST_API_SECRET_KEY,
        ]);
        $accountingOrganization->locations()->attach($location);

        $initSourceBalance = 100.00;

        /** Clearing account */
        GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganization->id,
            $increaseActionIsDebit = true,
            $initSourceBalance,
            $isBankAccount = false,
            GLAccount::CLEARING_ACCOUNT_CODE
        );

        /** Bank account */
        GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganization->id,
            $increaseActionIsDebit = true,
            $initSourceBalance = 0,
            $isBankAccount = true
        );

        $invoiceCount       = 1;
        $invoiceData        = [
            'accounting_organization_id' => $accountingOrganization->id,
        ];
        $withApproveRequest = true;

        $invoice = InvoicesTestFactory::createInvoices(
            $invoiceCount,
            $invoiceData,
            FinancialEntityStatuses::APPROVED,
            $withApproveRequest
        );

        $cost = $initSourceBalance + $this->faker->numberBetween(1, 10);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->first()->id,
            'unit_cost'  => $cost,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = [
            'name'             => $this->faker->name,
            'number'           => self::TEST_CARD_NUMBER_INSUFFICIENT_FUNDS,
            'cvv'              => $this->faker->numberBetween(100, 999),
            'email'            => $this->faker->email,
            'billing_address1' => $this->faker->address,
            'billing_city'     => $this->faker->city,
            'billing_country'  => $this->faker->country,
            'expiry_month'     => Carbon::now()->addMonth()->month,
            'expiry_year'      => Carbon::now()->addYear()->year,
        ];

        $url = action('Finance\InvoicesController@receiveCreditCardPayment', [
            'invoice' => $invoice->first()->id,
        ]);

        $this->postJson($url, $data)
            ->assertFailedDependency('There are not enough funds available to process the requested amount');

        Event::assertNotDispatched(CreditCardPaymentProcessedEvent::class);
    }

    /**
     * @throws \Throwable
     */
    public function testDirectDepositPayment(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDays(2)->day,
            'is_active'         => true,
        ]);
        /** @var GLAccount $receivableAccount */
        $receivableAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                //Asset type (Increasing is debit)
                'increase_action_is_debit' => true,
            ]),
            'enable_payments_to_account' => true,
        ]);
        /** @var GLAccount $salesAccount */
        $salesAccount          = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'increase_action_is_debit' => false,
            ]),
            'enable_payments_to_account' => true,
        ]);
        $businessChequeAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                //Asset type (Increasing is debit)
                'increase_action_is_debit' => true,
            ]),
            'enable_payments_to_account' => true,
        ]);

        $taxPayableAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'increase_action_is_debit' => false,
            ]),
            'enable_payments_to_account' => true,
        ]);

        $accountingOrganization->tax_payable_account_id         = $taxPayableAccount->id;
        $accountingOrganization->accounts_receivable_account_id = $receivableAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        $amount = $this->faker->randomFloat(2, 100, 1000);

        $taxRate = 0.1;
        /** @var InvoiceItem $invoiceItem */
        factory(InvoiceItem::class)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amount,
            'quantity'      => 1,
            'discount'      => 0,
            'gl_account_id' => $salesAccount->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        $totalAmount                       = $invoice->getTotalAmount();
        $this->user->invoice_approve_limit = $totalAmount;
        $this->user->saveOrFail();
        $this->user->locations()->attach($invoice->location_id);
        $accountingOrganization->locations()->attach($invoice->location_id);

        /** @var InvoicesService $invoiceService */
        $invoiceService = $this->app->make(InvoicesService::class);
        $invoiceService->approve($invoice->id, $this->user);

        $data     = [
            'amount'        => $totalAmount,
            'paid_at'       => Carbon::now()->toDateString(),
            'gl_account_id' => $businessChequeAccount->id,
            'reference'     => $this->faker->word,
        ];
        $url      = action('Finance\InvoicesController@receiveDirectDepositPayment', [
            'invoice' => $invoice->id,
        ]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(200);

        /** @var GLAccountServiceInterface $glAccountService */
        $glAccountService             = $this->app->make(GLAccountServiceInterface::class);
        $businessChequeAccountBalance = $glAccountService->getAccountBalance($businessChequeAccount->id);
        self::assertTrue(Decimal::areEquals(round($totalAmount, 2), $businessChequeAccountBalance));
        $receivableAccountBalance = $glAccountService->getAccountBalance($receivableAccount->id);
        self::assertTrue(Decimal::isZero($receivableAccountBalance));
    }
}
