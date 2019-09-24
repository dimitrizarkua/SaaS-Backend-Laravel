<?php

namespace Tests\API\Reporting;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Helpers\Decimal;
use App\Http\Responses\Reporting\IncomeReportResponse;
use Carbon\Carbon;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ReportingGLAccountsControllerTest
 *
 * @package Tests\API\Reporting
 * @group   gl-accounts
 * @group   income
 * @group   reporting
 */
class ReportingGLAccountsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.gl_accounts.reports.view',
    ];

    /** @var AccountingOrganization */
    protected $organization;

    public function setUp(): void
    {
        parent::setUp();
        $models       = [
            GLAccount::class,
            ForwardedPayment::class,
            ForwardedPaymentInvoice::class,
            LocationUser::class,
            AccountingOrganization::class,
            TaxRate::class,
            AccountTypeGroup::class,
            AccountType::class,
            AccountingOrganizationLocation::class,
        ];
        $this->models = array_merge($models, $this->models);

        $location           = factory(Location::class)->create();
        $this->organization = factory(AccountingOrganization::class)->create();
        $this->organization->locations()->attach($location->id);
        $this->user->locations()->attach($location->id);
    }

    public function testListTransactionByGLAccount(): void
    {
        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransaction', $filter);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $response->assertSeeData();
        $response->assertSeePagination();
        $response->assertJsonDataCount($transactionRecord->count());
    }

    public function testListTransactionByGLAccountShouldReturnResultSetLeftBoundary(): void
    {
        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon('first day of this month'),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransaction', $filter);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $response->assertSeeData();
        $response->assertSeePagination();
        $response->assertJsonDataCount($transactionRecord->count());
    }

    public function testListTransactionByGLAccountShouldReturnResultSetByRightBoundary(): void
    {
        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon('last day of this month'),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransaction', $filter);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $response->assertSeeData();
        $response->assertSeePagination();
        $response->assertJsonDataCount($transactionRecord->count());
    }

    public function testListTransactionByGLAccountShouldReturnEmptyResultOutOfBoundaries(): void
    {
        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => (new Carbon('last day of this month'))->addDay(),
        ]);

        $cnt = $this->faker->numberBetween(1, 5);
        factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransaction', $filter);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $response->assertSeeData();
        $response->assertSeePagination();
        $response->assertJsonDataCount(0);
    }

    public function testFailToGetListTransactionReportWhenUserBelongsToOtherLocation(): void
    {
        $location = factory(Location::class)->create();
        $this->organization->locations()->sync($location->id);

        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testListTransactionReportByGLAccountDebitOperation(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => true,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))
                ->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional()
            ->assertJsonDataCount($transactionRecord->count());

        $startBalance = $balance = 0;

        $respAdditional = $response->getAdditionalData();
        foreach ($transactionRecord as $r) {
            $balance      = bcsub($startBalance, $r->amount, 2);
            $startBalance = $balance;
        }

        self::assertEquals(bccomp($balance, $respAdditional['total_balance']), 0);
        self::assertEquals($cnt, $respAdditional['total_transactions']);
        self::assertEquals($glAccount->name, $respAdditional['gl_account']['name']);
    }

    public function testListTransactionReportByGLAccountCreditOperation(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))
                ->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional()
            ->assertJsonDataCount($transactionRecord->count());

        $startBalance = $balance = 0;

        $respAdditional = $response->getAdditionalData();
        foreach ($transactionRecord as $r) {
            $balance      = bcadd($startBalance, $r->amount, 2);
            $startBalance = $balance;
        }

        self::assertEquals(bccomp($balance, $respAdditional['total_balance']), 0);
        self::assertEquals($cnt, $respAdditional['total_transactions']);
        self::assertEquals($glAccount->name, $respAdditional['gl_account']['name']);
    }

    public function testListTransactionReportByGLAccountDebitOperationWithPreviousTransactions(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => true,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $prevTransaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => (new Carbon('first day of this month'))->subDay(),
        ]);

        $cnt                   = $this->faker->numberBetween(1, 3);
        $prevTransactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $prevTransaction->id,
            'is_debit'       => true,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 3);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => true,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional()
            ->assertJsonDataCount($transactionRecord->count());

        $prevStartBalance = 0;
        foreach ($prevTransactionRecord as $r) {
            $prevStartBalance = bcadd($prevStartBalance, $r->amount, 2);
        }

        $startBalance = $prevStartBalance;

        $balance = 0;

        $respData         = $response->getData();
        $firstTransaction = $respData[0];
        $respAdditional   = $response->getAdditionalData();

        foreach ($transactionRecord as $r) {
            $balance      = bcadd($startBalance, $r->amount, 2);
            $startBalance = $balance;
        }

        self::assertEquals($prevStartBalance, bcsub($firstTransaction['balance'], $firstTransaction['amount'], 2));
        self::assertEquals(bccomp($balance, $respAdditional['total_balance']), 0);
        self::assertEquals($cnt, $respAdditional['total_transactions']);
        self::assertEquals($glAccount->name, $respAdditional['gl_account']['name']);
    }

    public function testListTransactionReportByGLAccountCreditOperationWithPreviousTransactions(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $prevTransaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => (new Carbon('first day of this month'))->subDay(),
        ]);

        $cnt                   = $this->faker->numberBetween(1, 5);
        $prevTransactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $prevTransaction->id,
            'is_debit'       => false,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional()
            ->assertJsonDataCount($transactionRecord->count());

        $prevStartBalance = 0;
        foreach ($prevTransactionRecord as $r) {
            $prevStartBalance = bcadd($prevStartBalance, $r->amount, 2);
        }

        $startBalance = $prevStartBalance;

        $balance = 0;

        $respData         = $response->getData();
        $firstTransaction = $respData[0];
        $respAdditional   = $response->getAdditionalData();

        foreach ($transactionRecord as $r) {
            $balance      = bcadd($startBalance, $r->amount, 2);
            $startBalance = $balance;
        }

        self::assertEquals($prevStartBalance, bcsub($firstTransaction['balance'], $firstTransaction['amount'], 2));
        self::assertEquals(bccomp($balance, $respAdditional['total_balance']), 0);
        self::assertEquals($cnt, $respAdditional['total_transactions']);
        self::assertEquals($glAccount->name, $respAdditional['gl_account']['name']);
    }

    public function testListTransactionReportByGLAccountCreditOperationWithPreviousDebitTransactions(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $prevTransaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => (new Carbon('first day of this month'))->subDay(),
        ]);

        $cnt                   = $this->faker->numberBetween(1, 5);
        $prevTransactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $prevTransaction->id,
            'is_debit'       => true,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt               = $this->faker->numberBetween(1, 5);
        $transactionRecord = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional()
            ->assertJsonDataCount($transactionRecord->count());

        $prevStartBalance = 0;
        foreach ($prevTransactionRecord as $r) {
            $prevStartBalance = bcsub($prevStartBalance, $r->amount, 2);
        }

        $startBalance = $prevStartBalance;

        $balance = 0;

        $respData         = $response->getData();
        $firstTransaction = $respData[0];
        $respAdditional   = $response->getAdditionalData();

        foreach ($transactionRecord as $r) {
            $balance      = bcadd($startBalance, $r->amount, 2);
            $startBalance = $balance;
        }

        self::assertEquals($prevStartBalance, bcsub($firstTransaction['balance'], $firstTransaction['amount'], 2));
        self::assertEquals(bccomp($balance, $respAdditional['total_balance']), 0);
        self::assertEquals($cnt, $respAdditional['total_transactions']);
        self::assertEquals($glAccount->name, $respAdditional['gl_account']['name']);
    }

    public function testListTransactionReportWhenThereAreNoTransactionRecords(): void
    {
        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $this->organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $this->organization->id,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'date_from'     => (new Carbon('first day of this month'))->format('Y-m-d'),
            'date_to'       => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@listTransactionReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeeAdditional();

        $responseData = $response->getData();
        self::assertCount(0, $responseData);
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountFilteredByLocationId(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $filter = [
            'location_id' => $location->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertTrue(Decimal::areEquals($payment->amount, $responseData['total_amount']));
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountFilteredByDate(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        $date = $this->faker->date();
        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
                'date'                       => $date,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoice = $invoices->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoice->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $dateFrom = Carbon::createFromFormat('Y-m-d', $date)
            ->subDay()
            ->format('Y-m-d');

        $dateTo = Carbon::createFromFormat('Y-m-d', $date)
            ->addDay()
            ->format('Y-m-d');

        $filter = [
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'location_id' => $location->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertEquals($payment->amount, $responseData['total_amount']);
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountFilteredByGLAccountId(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoice = $invoices->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoice->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $filter = [
            'gl_account_id' => $glAccount->id,
            'location_id'   => $location->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertTrue(Decimal::areEquals($payment->amount, $responseData['total_amount']));
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountFilteredByContactId(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        $contact = factory(Contact::class)->create();

        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
                'recipient_contact_id'       => $contact->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoice = $invoices->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoice->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $filter = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertEquals($payment->amount, $responseData['total_amount']);
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountWhenAllFilterFieldsWereSet(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        $contact = factory(Contact::class)->create();
        $date    = $this->faker->date();
        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
                'recipient_contact_id'       => $contact->id,
                'date'                       => $date,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoice = $invoices->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoice->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $dateFrom = Carbon::createFromFormat('Y-m-d', $date)
            ->subDay()
            ->format('Y-m-d');

        $dateTo = Carbon::createFromFormat('Y-m-d', $date)
            ->addDay()
            ->format('Y-m-d');

        $filter = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
            'gl_account_id'        => $glAccount->id,
            'date_from'            => $dateFrom,
            'date_to'              => $dateTo,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertEquals($payment->amount, $responseData['total_amount']);
    }

    /**
     * @throws \Exception
     */
    public function testListIncomeByGLAccountWhenAllFilterFieldsWereSetAndWithForwardedPayment(): void
    {
        $location = factory(Location::class)->create();

        factory(LocationUser::class)->create([
            'user_id'     => $this->user->id,
            'location_id' => $location->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingOrganizationLocation */
        $accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)
            ->create([
                'location_id' => $location->id,
            ]);

        /** @var AccountTypeGroup $accountTypeGroup */
        $accountTypeGroup = factory(AccountTypeGroup::class)
            ->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);

        $glAccount = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        $contact = factory(Contact::class)->create();
        $date    = $this->faker->date();

        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
                'recipient_contact_id'       => $contact->id,
                'date'                       => $date,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoice = $invoices->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoice->id,
                'gl_account_id' => $glAccount->id,
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount'     => $payment->amount,
            'is_fp'      => true,
        ]);

        $forwardedPayment = factory(ForwardedPayment::class)->create([
            'payment_id'           => $payment->id,
            'remittance_reference' => 'text',
            'transferred_at'       => Carbon::now(),
        ]);

        factory(ForwardedPaymentInvoice::class)->create([
            'forwarded_payment_id' => $forwardedPayment->id,
            'invoice_id'           => $invoice->id,
            'amount'               => $payment->amount,
        ]);

        $dateFrom = Carbon::createFromFormat('Y-m-d', $date)
            ->subDay()
            ->format('Y-m-d');

        $dateTo = Carbon::createFromFormat('Y-m-d', $date)
            ->addDay()
            ->format('Y-m-d');

        $filter = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
            'gl_account_id'        => $glAccount->id,
            'date_from'            => $dateFrom,
            'date_to'              => $dateTo,
        ];

        $url = action('Reporting\ReportingGLAccountsController@listIncomeReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(IncomeReportResponse::class, true);

        $responseData = $response->getData();
        self::assertNotEmpty($responseData);
        self::assertEquals($glAccount->accountType->name, $responseData['account_types'][0]['name']);
        self::assertTrue(Decimal::isZero($responseData['total_amount']));
        self::assertTrue(Decimal::areEquals(round($payment->amount, 2), $responseData['total_forwarded_amount']));
    }

    public function testTrialReportSuccess(): void
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $accountTypeGroups = [
            AccountTypeGroups::ASSET     => factory(AccountTypeGroup::class)
                ->create(['name' => AccountTypeGroups::ASSET]),
            AccountTypeGroups::LIABILITY => factory(AccountTypeGroup::class)
                ->create(['name' => AccountTypeGroups::LIABILITY]),
            AccountTypeGroups::REVENUE   => factory(AccountTypeGroup::class)
                ->create(['name' => AccountTypeGroups::REVENUE]),
        ];

        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
            'account_type_group_id'    => $accountTypeGroups[AccountTypeGroups::ASSET]->id,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => $date = $this->faker->date(),
        ]);

        factory(TransactionRecord::class)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => $this->faker->boolean,
            'amount'         => $this->faker->numberBetween(20, 100),
        ]);

        $locationId = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization,
        ])->location_id;

        $filter = [
            'location_id' => $locationId,
            'date_to'     => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@trialReport', $filter);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $responseData = $response->getData();
        self::assertCount(count($accountTypeGroups) + 1, $responseData);
    }

    public function testTrialReportEmptyLocationId(): void
    {
        $filter = [
            'location_id' => null,
            'date_to'     => (new Carbon('last day of this month'))
                ->addDay()
                ->format('Y-m-d'),
        ];

        $url = action('Reporting\ReportingGLAccountsController@trialReport', $filter);

        $this->getJson($url)->assertStatus(422);
    }

    public function testTrialReportLocationIdWithoutAccountingOrganization(): void
    {
        $locationId = factory(Location::class)->create()->id;

        $filter = [
            'location_id' => $locationId,
            'date_to'     => null,
        ];

        $url = action('Reporting\ReportingGLAccountsController@trialReport', $filter);

        $this->getJson($url)->assertStatus(422);
    }
}
