<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\TaxRates;
use App\Components\Finance\Events\InvoiceCreated;
use App\Components\Finance\Events\InvoiceDeleted;
use App\Components\Finance\Events\InvoicePaymentCreated;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\InvoiceTransaction;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\CreateInvoiceData;
use App\Components\Finance\Models\VO\CreateInvoicePaymentsData;
use App\Components\Finance\Models\VO\ReceivePaymentData;
use App\Components\Finance\Services\InvoicesService;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Helpers\Decimal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class InvoicesServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   finance
 * @group   invoices
 */
class InvoicesServiceTest extends TestCase
{
    /**
     * @var InvoicesService
     */
    private $service;

    /**
     * @var AccountingOrganization
     */
    private $accountingOrganization;

    /**
     * @var GLAccount
     */
    private $bankAccount;

    /**
     * @var GLAccount
     */
    private $machinesAccount;

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
            User::class,
            ContactCompanyProfile::class,
            InvoicePayment::class,
            InvoiceTransaction::class,
        ], $this->models);

        $this->service = $this->app->make(InvoicesService::class);

        $assetsAccountType = factory(AccountType::class)->create([
            'name'                     => 'Asset',
            'increase_action_is_debit' => true,
        ]);

        $this->accountingOrganization = factory(AccountingOrganization::class)->create(['is_active' => true]);
        $this->bankAccount            = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
            'enable_payments_to_account' => true,
        ]);
        $this->machinesAccount        = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
            'enable_payments_to_account' => true,
        ]);
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
            'contact_id'        => $contact->id,
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $accountingOrganization->payment_details_account_id = $paymentDetailsAccount->id;
        $accountingOrganization->saveOrFail();

        $recipientContact = factory(Contact::class)->create();
        /** @var GLAccount $bankAccount */
        $bankAccount       = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);
        $createInvoiceData = new CreateInvoiceData([
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'recipient_address'    => $this->faker->address,
            'recipient_name'       => $this->faker->name,
            'job_id'               => null,
            'document_id'          => null,
            'due_at'               => Carbon::now()->addDays(4),
            'date'                 => Carbon::now(),
            'reference'            => $this->faker->word,
            'items'                => [
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'discount = 0'  => $this->faker->randomFloat(2, 10, 50),
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                ],
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'discount = 0'  => $this->faker->randomFloat(2, 10, 50),
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                ],
            ],
        ]);

        $this->expectsEvents(InvoiceCreated::class);
        $invoice  = $this->service->create($createInvoiceData);
        $reloaded = Invoice::find($invoice->id);
        self::assertNotNull($reloaded);
        self::assertEquals($createInvoiceData->location_id, $reloaded->location_id);
        self::assertEquals($accountingOrganization->id, $reloaded->accounting_organization_id);
        self::assertEquals($createInvoiceData->recipient_contact_id, $reloaded->recipient_contact_id);
        self::assertEquals($createInvoiceData->recipient_address, $reloaded->recipient_address);
        self::assertEquals($createInvoiceData->recipient_name, $reloaded->recipient_name);
        self::assertEquals($createInvoiceData->reference, $reloaded->reference);
        self::assertEquals(FinancialEntityStatuses::DRAFT, $reloaded->latestStatus->status);
        self::assertCount(2, $reloaded->items);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     */
    public function testDeleteDraftInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        $invoiceId = $invoice->id;
        $this->expectsEvents(InvoiceDeleted::class);
        $this->service->delete($invoice->id);

        $model = Invoice::find($invoiceId);
        self::assertNull($model);
    }

    /**
     * @throws \Throwable
     */
    public function testExceptionShouldBeThrownWhenInvoiceWithNonDraftStatusDeleting(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => 'NON_DRAFT_STATUS',
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Invoice can\'t be deleted because it can\'t be modified or has approve requests.'
        );
        $this->service->delete($invoice->id);
    }

    /**
     * @throws \Throwable
     */
    public function testExceptionShouldBeThrownWhenInvoiceHasApproveRequestDeleting(): void
    {
        $invoice = InvoicesTestFactory::createDraftInvoice();
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id' => $invoice->id,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Invoice can\'t be deleted because it can\'t be modified or has approve requests.'
        );
        $this->service->delete($invoice->id);
    }

    /**
     * @throws \Throwable
     */
    public function testApproveInvoice(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
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

        $taxRate  = 0.1;
        $quantity = $this->faker->numberBetween(2, 4);
        factory(InvoiceItem::class)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amount,
            'quantity'      => $quantity,
            'discount'      => 0,
            'gl_account_id' => $salesAccount->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'invoice_approve_limit' => $invoice->getTotalAmount(),
        ]);
        $user->locations()->attach($invoice->location->id);
        //Create approve request
        $countOfRequests = $this->faker->numberBetween(2, 4);
        factory(InvoiceApproveRequest::class, $countOfRequests)
            ->create([
                'invoice_id' => $invoice->id,
            ]);
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id'  => $invoice->id,
            'approver_id' => $user->id,
        ]);

        $this->service->approve($invoice->id, $user);

        $reloaded = Invoice::find($invoice->id);
        self::assertEquals(FinancialEntityStatuses::APPROVED, $reloaded->latestStatus->status);
        self::assertNotNull($reloaded->latestStatus->user_id);
        self::assertEquals($user->id, $reloaded->latestStatus->user_id);

        $approveRequestList = InvoiceApproveRequest::query()
            ->where('invoice_id', $invoice->id)
            ->get();

        self::assertCount(1, $approveRequestList);
        $approveRequest = $approveRequestList->first();
        self::assertNotNull($approveRequest->approved_at);

        $salesAccount->fresh();
        $receivableAccount->fresh();

        /** @var GLAccountServiceInterface $glAccountService */
        $glAccountService = app()->make(GLAccountServiceInterface::class);

        $balanceOfReceivableAccount = $glAccountService->getAccountBalance($receivableAccount->id);
        self::assertTrue(Decimal::areEquals($balanceOfReceivableAccount, round($reloaded->getTotalAmount(), 2)));
        $balanceOfSalesAccount = $glAccountService->getAccountBalance($salesAccount->id);
        self::assertTrue(Decimal::areEquals($balanceOfSalesAccount, round($reloaded->getSubTotalAmount(), 2)));
        $balanceOfTaxesAccount = $glAccountService->getAccountBalance($taxPayableAccount->id);
        self::assertTrue(Decimal::areEquals($balanceOfTaxesAccount, round($reloaded->getTaxesAmount(), 2)));
    }

    /**
     * @see https://pushstack.atlassian.net/browse/SN-893
     *
     * @throws \Throwable
     */
    public function testCountOfTransactionRecordsShouldBeOnlyOneForTaxPayableAccount(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
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

        $taxRate          = 0.1;
        $quantity         = $this->faker->numberBetween(2, 4);
        $invoiceItemCount = $this->faker->numberBetween(2, 3);

        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amount,
            'quantity'      => $quantity,
            'discount'      => 0,
            'gl_account_id' => $salesAccount->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'invoice_approve_limit' => $invoice->getTotalAmount(),
        ]);
        $user->locations()->attach($invoice->location->id);
        //Create approve request
        $countOfRequests = $this->faker->numberBetween(2, 4);
        factory(InvoiceApproveRequest::class, $countOfRequests)
            ->create([
                'invoice_id' => $invoice->id,
            ]);
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id'  => $invoice->id,
            'approver_id' => $user->id,
        ]);

        $this->service->approve($invoice->id, $user);

        $transactionRecordCount = TransactionRecord::count();
        // 1 ($receivableAccount) + one for sales items + 1 ($taxPayableAccount)
        self::assertEquals(3, $transactionRecordCount);
    }

    /**
     * @see https://pushstack.atlassian.net/browse/SN-931
     *
     * @throws \Throwable
     */
    public function testCountOfTransactionRecordsShouldBeGroupedByGLAccount(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        /** @var GLAccount $receivableAccount */
        $receivableAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                //Asset type (Increasing is debit)
                'increase_action_is_debit' => true,
            ]),
        ]);
        /** @var GLAccount $salesAccountOne */
        $salesAccountOne = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'increase_action_is_debit' => false,
            ]),
        ]);

        /** @var GLAccount $salesAccountTwo */
        $salesAccountTwo = factory(GLAccount::class)->create([
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

        $amountOne = $this->faker->randomFloat(2, 100, 1000);
        $amountTwo = $this->faker->randomFloat(2, 100, 1000);

        $taxRate          = 0.1;
        $quantity         = $this->faker->numberBetween(2, 4);
        $invoiceItemCount = $this->faker->numberBetween(2, 3);

        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amountOne,
            'quantity'      => $quantity,
            'discount'      => 0,
            'gl_account_id' => $salesAccountOne->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id'    => $invoice->id,
            'unit_cost'     => $amountTwo,
            'quantity'      => $quantity,
            'discount'      => 0,
            'gl_account_id' => $salesAccountTwo->id,
            'tax_rate_id'   => factory(TaxRate::class)->create([
                'name' => TaxRates::GST_ON_INCOME,
                'rate' => $taxRate,
            ]),
        ]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'invoice_approve_limit' => $invoice->getTotalAmount(),
        ]);
        $user->locations()->attach($invoice->location->id);
        //Create approve request
        $countOfRequests = $this->faker->numberBetween(2, 4);
        factory(InvoiceApproveRequest::class, $countOfRequests)
            ->create([
                'invoice_id' => $invoice->id,
            ]);
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id'  => $invoice->id,
            'approver_id' => $user->id,
        ]);

        $this->service->approve($invoice->id, $user);

        $transactionRecordCount = TransactionRecord::count();
        // 1 ($receivableAccount) + 1 for $salesAccountOne +  1 for $salesAccountTwo + 1 ($taxPayableAccount)
        self::assertEquals(4, $transactionRecordCount);
    }

    /**
     * @throws \Throwable
     */
    public function testApproveRequestsForInvoiceShouldBeCreated(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $invoiceApprovalLimit   = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location        = factory(Location::class)->create();
        $countOfApprover = $this->faker->numberBetween(2, 4);
        /** @var \Illuminate\Support\Collection $approverList */
        $approverList = factory(User::class, $countOfApprover)
            ->create([
                'invoice_approve_limit' => $invoiceApprovalLimit,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        /** @var User $requester */
        $requester = factory(User::class)->create();
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'location_id'                => $location->id,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id'  => $invoice->id,
            'unit_cost'   => $invoiceApprovalLimit - 1,
            'quantity'    => 1,
            'discount'    => 0,
            'tax_rate_id' => factory(TaxRate::class)->create(['rate' => 0])->id,
        ]);

        $approverIdsList = $approverList->pluck('id')
            ->toArray();
        $this->service->createApproveRequest($invoice->id, $requester->id, $approverIdsList);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertCount($countOfApprover, $reloaded->approveRequests);
    }

    /**
     * @throws \Throwable
     */
    public function testApproveRequestsForInvoiceShouldBeCreatedTwice(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $invoiceApprovalLimit   = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location = factory(Location::class)->create();

        /** @var User $requester */
        $requester = factory(User::class)->create();
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'location_id'                => $location->id,
            'locked_at'                  => Carbon::now(),
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id'  => $invoice->id,
            'unit_cost'   => $invoiceApprovalLimit - 1,
            'quantity'    => 1,
            'discount'    => 0,
            'tax_rate_id' => factory(TaxRate::class)->create(['rate' => 0])->id,
        ]);

        $countOfApprover        = $this->faker->numberBetween(2, 4);
        $countOfExistingRequest = $this->faker->numberBetween(2, 4);

        $approverIdsList = factory(User::class, $countOfExistingRequest)
            ->create([
                'invoice_approve_limit' => $invoiceApprovalLimit,
            ])
            ->each(function (User $user) use ($location, $invoice, $requester) {
                $user->locations()->attach($location);
                factory(InvoiceApproveRequest::class)->create([
                    'invoice_id'   => $invoice->id,
                    'requester_id' => $requester->id,
                    'approver_id'  => $user->id,
                    'approved_at'  => null,
                ]);
            })
            ->merge(
                factory(User::class, $countOfApprover)
                    ->create([
                        'invoice_approve_limit' => $invoiceApprovalLimit,
                    ])
                    ->each(function (User $user) use ($location) {
                        $user->locations()->attach($location);
                    })
            )
            ->pluck('id')
            ->toArray();

        $this->service->createApproveRequest($invoice->id, $requester->id, $approverIdsList);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertCount($countOfApprover + $countOfExistingRequest, $reloaded->approveRequests);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApproveInvoiceWhenUserBelongsToWrongLocation(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $invoiceApprovalLimit   = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location        = factory(Location::class)->create();
        $anotherLocation = factory(Location::class)->create();

        /** @var User $approver */
        $approver = factory(User::class)->create([
            'invoice_approve_limit' => $invoiceApprovalLimit,
        ]);
        $approver->locations()->attach($anotherLocation);

        $invoice = InvoicesTestFactory::createDraftInvoice([
            'accounting_organization_id' => $accountingOrganization->id,
            'location_id'                => $location->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $invoiceApprovalLimit - 1,
            'quantity'   => 1,
            'discount'   => 0,
        ]);
        $requester = factory(User::class)->create();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'User [%d] can\'t be an approver.',
            $approver->id
        ));
        $this->service->createApproveRequest($invoice->id, $requester->id, [$approver->id]);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertEmpty($reloaded->approveRequests);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApproveInvoiceWhenUserHasWrongApprovalLimit(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $invoiceApprovalLimit   = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var User $approver */
        $approver = factory(User::class)->create([
            'invoice_approve_limit' => $invoiceApprovalLimit - 1,
        ]);
        $approver->locations()->attach($location);

        $invoice = InvoicesTestFactory::createDraftInvoice([
            'accounting_organization_id' => $accountingOrganization->id,
            'location_id'                => $location->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $invoiceApprovalLimit,
            'quantity'   => 1,
            'discount'   => 0,
        ]);
        $requester = factory(User::class)->create();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'User [%d] can\'t be an approver.',
            $approver->id
        ));
        $this->service->createApproveRequest($invoice->id, $requester->id, [$approver->id]);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertEmpty($reloaded->approveRequests);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithDirectDepositPayment(): void
    {
        $amount    = $this->faker->randomFloat(2, 10, 1000);
        $paidAt    = Carbon::today();
        $reference = $this->faker->words(3, true);
        $invoice   = factory(Invoice::class)->create();
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $amount,
                'paidAt'                   => $paidAt,
                'reference'                => $reference,
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount'     => $amount,
                ],
            ],
        ]);

        $this->service->payWithDirectDepositPayment($data);

        $reloaded = Invoice::findOrFail($invoice->id);
        /** @var Payment $payment */
        $payment = $reloaded->payments->first();

        self::assertCount(1, $reloaded->payments);
        self::assertEquals($amount, $payment->pivot->amount);
        self::assertEquals($paidAt, $payment->paid_at);
        self::assertEquals($reference, $payment->reference);
        self::assertCount(1, $reloaded->transactions);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithDirectDepositPaymentWithFP(): void
    {
        $amount  = $this->faker->randomFloat(2, 10, 1000);
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $amount,
                'paidAt'                   => Carbon::now(),
                'reference'                => 'Payment for invoice',
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount'     => $amount,
                    'is_fp'      => true,
                ],
            ],
        ]);

        $this->service->payWithDirectDepositPayment($data);

        $reloaded = Invoice::findOrFail($invoice->id);
        self::assertCount(1, $reloaded->payments);
        self::assertEquals($amount, $reloaded->payments->first()->pivot->amount);
        self::assertEquals(true, $reloaded->payments->first()->pivot->is_fp);
        self::assertCount(1, $reloaded->transactions);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithDirectDepositPaymentForMultipleInvoices(): void
    {
        $totalAmount      = 0;
        $numberOfInvoices = $this->faker->numberBetween(2, 4);
        $invoicesList     = [];
        for ($i = 0; $i < $numberOfInvoices; $i++) {
            $amount  = $this->faker->randomFloat(2, 10, 1000);
            $invoice = factory(Invoice::class)->create();
            factory(InvoiceItem::class)->create([
                'invoice_id' => $invoice->id,
                'unit_cost'  => $amount,
                'quantity'   => 1,
                'discount'   => 0,
            ]);

            $totalAmount += $amount;

            $invoicesList[] = [
                'invoice_id' => $invoice->id,
                'amount'     => $amount,
                'is_fp'      => $this->faker->boolean,
            ];
        }

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $totalAmount,
                'paidAt'                   => Carbon::now(),
                'reference'                => 'Payment for invoices',
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => $invoicesList,
        ]);

        $payment = $this->service->payWithDirectDepositPayment($data);

        $invoices = Invoice::query()
            ->whereHas('payments', function (Builder $query) use ($payment) {
                $query->where('id', $payment->id);
            })
            ->get();

        self::assertCount($numberOfInvoices, $invoices);
        foreach ($invoicesList as $invoiceItem) {
            /** @var Invoice $invoice */
            $invoice = $invoices->where('id', $invoiceItem['invoice_id'])
                ->first();
            self::assertNotNull($invoice);
            self::assertCount(1, $invoice->transactions);
            self::assertEquals($invoiceItem['amount'], $invoice->payments->first()->pivot->amount);
            self::assertEquals($invoiceItem['is_fp'], $invoice->payments->first()->pivot->is_fp);
        }
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithCreditNote(): void
    {
        $amount    = $this->faker->randomFloat(2, 10, 1000);
        $paidAt    = Carbon::today();
        $reference = $this->faker->words(3, true);
        $invoice   = factory(Invoice::class)->create();
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $amount,
                'paidAt'                   => $paidAt,
                'reference'                => $reference,
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount'     => $amount,
                ],
            ],
        ]);

        $this->service->payWithCreditNote($data);

        $reloaded = Invoice::findOrFail($invoice->id);
        /** @var Payment $payment */
        $payment = $reloaded->payments->first();
        self::assertCount(1, $reloaded->payments);
        self::assertEquals($amount, $payment->amount);
        self::assertEquals($paidAt, $payment->paid_at);
        self::assertEquals($reference, $payment->reference);
        self::assertCount(1, $reloaded->transactions);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();

        $data = [
            'location_id' => factory(Location::class)->create()->id,
            'job_id'      => factory(Job::class)->create()->id,
            'reference'   => $this->faker->word,
        ];

        /** @var Invoice $model */
        $model = $this->service->update($invoice->id, $data);

        //Assert that location id was not changed
        self::assertEquals($invoice->location_id, $model->location_id);
        self::assertEquals($data['reference'], $model->reference);
        self::assertEquals($data['job_id'], $model->job_id);
    }

    /**
     * @throws \Throwable
     */
    public function tesRecipientAddressAndNameShouldBeChangedWhenUpdateInvoiceRecipient(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = [
            'recipient_contact_id' => $recipientContact->id,
        ];

        /** @var Invoice $model */
        $model = $this->service->update($invoice->id, $data);

        self::assertEquals($data['recipient_contact_id'], $model->recipient_contact_id);
        self::assertEquals($address->full_address, $model->recipient_address);
        self::assertEquals($recipientContact->getContactName(), $model->recipient_name);
    }

    /**
     * @throws \Throwable
     */
    public function testRecipientAddressAndNameShouldBeChangedIfPassedWhenUpdateInvoiceRecipient(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = [
            'recipient_contact_id' => $recipientContact->id,
            'recipient_address'    => $this->faker->address,
            'recipient_name'       => $this->faker->name,
        ];

        /** @var Invoice $model */
        $model = $this->service->update($invoice->id, $data);

        self::assertEquals($data['recipient_contact_id'], $model->recipient_contact_id);
        self::assertEquals($address->full_address, $model->recipient_address);
        self::assertEquals($recipientContact->getContactName(), $model->recipient_name);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateInvoiceWhenRecipientContactHasNoAddresses(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();

        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();

        $data = [
            'recipient_contact_id' => $recipientContact->id,
        ];

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Recipient contact should has at least one attached address.'
        );
        $this->service->update($invoice->id, $data);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithDirectDepositWithFireEvent(): void
    {
        $amount    = $this->faker->randomFloat(2, 10, 1000);
        $paidAt    = Carbon::today();
        $reference = $this->faker->words(3, true);
        $invoice   = factory(Invoice::class)->create();
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $amount,
                'paidAt'                   => $paidAt,
                'reference'                => $reference,
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount'     => $amount,
                ],
            ],
        ]);

        $this->expectsEvents(InvoicePaymentCreated::class);
        $this->service->payWithDirectDepositPayment($data);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testPayWithCreditNoteWithFireEvent(): void
    {
        $amount    = $this->faker->randomFloat(2, 10, 1000);
        $paidAt    = Carbon::today();
        $reference = $this->faker->words(3, true);
        $invoice   = factory(Invoice::class)->create();
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = new CreateInvoicePaymentsData([
            'payment_data'  => [
                'amount'                   => $amount,
                'paidAt'                   => $paidAt,
                'reference'                => $reference,
                'accountingOrganizationId' => $this->accountingOrganization->id,
                'payableGLAccountList'     => [
                    [
                        'glAccount' => $this->bankAccount,
                        'amount'    => $amount,
                    ],
                ],
                'receivableGLAccountList'  => [
                    [
                        'glAccount' => $this->machinesAccount,
                        'amount'    => $amount,
                    ],
                ],
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount'     => $amount,
                ],
            ],
        ]);

        $this->expectsEvents(InvoicePaymentCreated::class);
        $this->service->payWithCreditNote($data);
    }

    /**
     * @throws \Throwable
     */
    public function testReceivePaymentDstAccountIncreaseActionIsDebitFalse(): void
    {
        $location = factory(Location::class)->create();
        $this->accountingOrganization->locations()->attach($location);
        $this->accountingOrganization->saveOrFail();

        $numberOfInvoices = $this->faker->numberBetween(2, 4);

        $amountFp = $amountDp = 0;

        for ($i = 0; $i < $numberOfInvoices; $i++) {
            $amount  = $this->faker->randomFloat(2, 10, 1000);
            $invoice = factory(Invoice::class)->create();
            factory(InvoiceItem::class)->create([
                'invoice_id' => $invoice->id,
                'unit_cost'  => $amount,
                'quantity'   => 1,
                'discount'   => 0,
            ]);

            $isFp = $this->faker->boolean;

            if ($isFp) {
                $amountFp += $amount;
            } else {
                $amountDp += $amount;
            }

            $invoicesList[] = [
                'invoice_id' => $invoice->id,
                'amount'     => $amount,
                'is_fp'      => $isFp,
            ];
        }

        $totalAmount = $amountDp + $amountFp;

        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = true,
            $balance = $totalAmount,
            $isBankAccount = true
        );

        $this->accountingOrganization->accounts_receivable_account_id = $glAccountSrc->id;
        $this->accountingOrganization->saveOrFail();

        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = $amountDp,
            $isBankAccount = true
        );

        $glAccountDstFp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = $amountFp,
            $isBankAccount = true,
            $code = GLAccount::FRANCHISE_PAYMENTS_ACCOUNT_CODE
        );

        $data = [
            'payment_data'      => [
                'paid_at'   => Carbon::now(),
                'reference' => 'Payment for invoices',
            ],
            'invoices_list'     => $invoicesList,
            'dst_gl_account_id' => $glAccountDstDp->id,
            'location_id'       => $location->id,
        ];

        $this->service->receiveInvoicePayment(new ReceivePaymentData($data));

        $glAccountService = app()->make(GLAccountServiceInterface::class);

        $srcGLAccountBalance = $glAccountService->getAccountBalance($glAccountSrc->id);
        self::assertTrue(Decimal::areEquals($srcGLAccountBalance, 0));

        $dstGLAccountBalanceDp = $glAccountService->getAccountBalance($glAccountDstDp->id);
        self::assertTrue(Decimal::areEquals($dstGLAccountBalanceDp, 0));

        $dstGLAccountBalanceFp = $glAccountService->getAccountBalance($glAccountDstFp->id);
        self::assertTrue(Decimal::areEquals($dstGLAccountBalanceFp, 0));
    }

    /**
     * @throws \Throwable
     */
    public function testReceivePaymentDstAccountIncreaseActionIsDebitTrue(): void
    {
        $location = factory(Location::class)->create();
        $this->accountingOrganization->locations()->attach($location);
        $this->accountingOrganization->saveOrFail();

        $numberOfInvoices = $this->faker->numberBetween(2, 4);

        $amountFp = $amountDp = 0;

        for ($i = 0; $i < $numberOfInvoices; $i++) {
            $amount  = $this->faker->randomFloat(2, 10, 1000);
            $invoice = factory(Invoice::class)->create();
            factory(InvoiceItem::class)->create([
                'invoice_id' => $invoice->id,
                'unit_cost'  => $amount,
                'quantity'   => 1,
                'discount'   => 0,
            ]);

            $isFp = $this->faker->boolean;

            if ($isFp) {
                $amountFp += $amount;
            } else {
                $amountDp += $amount;
            }

            $invoicesList[] = [
                'invoice_id' => $invoice->id,
                'amount'     => $amount,
                'is_fp'      => $isFp,
            ];
        }

        $totalAmount = $amountDp + $amountFp;

        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = true,
            $balance = $totalAmount,
            $isBankAccount = true
        );

        $this->accountingOrganization->accounts_receivable_account_id = $glAccountSrc->id;
        $this->accountingOrganization->saveOrFail();

        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = true
        );

        $glAccountDstFp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = $amountFp,
            $isBankAccount = true,
            $code = GLAccount::FRANCHISE_PAYMENTS_ACCOUNT_CODE
        );

        $data = [
            'payment_data'      => [
                'paid_at'   => Carbon::now(),
                'reference' => 'Payment for invoices',
            ],
            'invoices_list'     => $invoicesList,
            'dst_gl_account_id' => $glAccountDstDp->id,
            'location_id'       => $location->id,
        ];

        $this->service->receiveInvoicePayment(new ReceivePaymentData($data));

        $glAccountService = app()->make(GLAccountServiceInterface::class);

        $srcGLAccountBalance = $glAccountService->getAccountBalance($glAccountSrc->id);
        self::assertTrue(Decimal::areEquals($srcGLAccountBalance, 0));

        $dstGLAccountBalanceDp = $glAccountService->getAccountBalance($glAccountDstDp->id);
        self::assertTrue(Decimal::areEquals($dstGLAccountBalanceDp, $amountDp));

        $dstGLAccountBalanceFp = $glAccountService->getAccountBalance($glAccountDstFp->id);
        self::assertTrue(Decimal::areEquals($dstGLAccountBalanceFp, 0));
    }
}
