<?php

namespace Tests\Unit\Reporting\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Reporting\Interfaces\IncomeReportServiceInterface;
use App\Components\Reporting\Models\Filters\IncomeReportFilter;
use App\Helpers\Decimal;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ReportingIncomeServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   finance
 * @group   reporting
 */
class ReportingIncomeServiceTest extends TestCase
{
    /**
     * @var \App\Components\Reporting\Interfaces\IncomeReportServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $models       = [
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountTypeGroup::class,
            AccountingOrganization::class,
            Contact::class,
            InvoiceItem::class,
            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
            AccountingOrganizationLocation::class,
            GSCode::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);

        $this->service = Container::getInstance()
            ->make(IncomeReportServiceInterface::class);
    }

    /**
     * Creates one invoice with one invoice item.
     * Expects that payment amount = total_amount = subtotal = item amount ex tax.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testOneInvoiceOnePaymentOneItem(): void
    {
        $location = factory(Location::class)->create();
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

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);

        self::assertCount(1, $reportData['account_types']);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        self::assertEquals($glAccount->name, $reportData['account_types'][0]['accounts']['items'][0]['name']);
        self::assertCount(1, $reportData['account_types'][0]['accounts']['items']);
        self::assertEquals($payment->amount, $reportData['account_types'][0]['accounts']['items'][0]['amount_ex_tax']);

        self::assertTrue(Decimal::areEquals($payment->amount, $reportData['total_amount']));
    }

    /**
     * Creates one invoice with two invoice items and two gl accounts.
     * Invoice item1 -> gl account 1.
     * Invoice item2 -> gl account 2.
     * Expects that response will contain two account_types with only one account item for each.
     * And total amount = sum of subtotal and subtotal = sum of amount_ex_tax.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testOneInvoiceOnePaymentTwoItemsTwoGLAccounts(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            AccountTypeGroups::REVENUE
        );

        $glAccount2 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            AccountTypeGroups::REVENUE
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

        $unitCost = $this->faker->randomFloat(2, 10, 20);
        $discount = $this->faker->randomFloat(2, 1, 5);

        /** @var InvoiceItem $invoiceItem1 */
        $invoiceItem1 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discount,
                'quantity'      => $this->faker->numberBetween(1, 5),
            ]);

        /** @var InvoiceItem $invoiceItem2 */
        $invoiceItem2 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount2->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discount,
                'quantity'      => $this->faker->numberBetween(1, 5),
            ]);

        /** @var Payment $payment */
        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem1->getSubTotal() + $invoiceItem2->getSubTotal(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        self::assertEquals($glAccount1->accountType->name, $reportData['account_types'][0]['name']);
        self::assertEquals($glAccount2->accountType->name, $reportData['account_types'][0]['name']);

        $subtotal = $reportAccountItems[0]['amount_ex_tax'] + $reportAccountItems[1]['amount_ex_tax'];
        $total    = $reportData['total_amount'];

        self::assertTrue(Decimal::areEquals($total, $subtotal));
        self::assertTrue(Decimal::areEquals($payment->amount, $subtotal));
    }

    /**
     * Creates one invoice with one invoice item and one gl account and with two equal payments.
     * Expects that subtotal will be equal to sum of payments and that total will be equal to sum of payments too.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testOneInvoiceTwoPaymentsOneItemOneGLAccount(): void
    {
        $location = factory(Location::class)->create();

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

        $unitCost          = 100;
        $discountInPercent = 10;

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 45,
        ]);

        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 45,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment2->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment2->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $subtotal           = $reportAccountItems[0]['amount_ex_tax'];

        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $reportData['total_amount']));
        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $subtotal));
    }

    /**
     * Creates one invoice with two items and one gl account.
     * Expects that total = subtotal = amount_ex_tax.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testOneInvoiceOnePaymentTwoItemsOneGLAccount(): void
    {
        $location = factory(Location::class)->create();

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

        $unitCost          = $this->faker->randomFloat(2, 10, 20);
        $discountInPercent = $this->faker->randomFloat(2, 1, 5);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoices->first()->id,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => ($unitCost - ($unitCost * ($discountInPercent / 100))) * 3,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $subtotal           = $reportAccountItems[0]['amount_ex_tax'];
        $total              = $reportData['total_amount'];

        self::assertTrue(Decimal::areEquals($payment1->amount, $total));
        self::assertTrue(Decimal::areEquals($payment1->amount, $subtotal));
    }

    /**
     * Creates two invoices with two items and one payment and one gl account.
     * Expects that payment amount = total = subtotal = amount_ex_tax.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTwoInvoiceTwoItemsOnePaymentOneGLAccount(): void
    {
        $location = factory(Location::class)->create();

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
            $unpaidInvoicesCount = 2,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $invoiceId2 = $invoices->last()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 90 * 6,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoiceId2,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $amountExTax        = $reportAccountItems[0]['amount_ex_tax'];
        $subTotal           = $reportData['account_types'][0]['accounts']['subtotal_amount'];
        $total              = $reportData['total_amount'];

        self::assertTrue(Decimal::areEquals($payment1->amount, $amountExTax));
        self::assertTrue(Decimal::areEquals($payment1->amount, $subTotal));
        self::assertTrue(Decimal::areEquals($payment1->amount, $total));
    }

    /**
     * Creates two invoices with two items and two payments and one gl account.
     * Expects that payment amount = total = subtotal = amount_ex_tax.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTwoInvoicesTwoPaymentsTwoItems(): void
    {
        $location = factory(Location::class)->create();

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
            $unpaidInvoicesCount = 2,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $invoiceId2 = $invoices->last()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 3 * 90,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 3 * 90,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment2->id,
            'invoice_id' => $invoiceId2,
            'amount'     => $payment2->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $amountExTax        = $reportAccountItems[0]['amount_ex_tax'];
        $subTotal           = $reportData['account_types'][0]['accounts']['subtotal_amount'];
        $total              = $reportData['total_amount'];

        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $amountExTax));
        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $subTotal));
        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $total));
    }

    /**
     * Creates two invoices with two items and two payments one gl account with forwarded payment.
     * Expects that total = sum of payments - forwarded amount.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTwoInvoicesTwoPaymentsTwoItemsAndForwardedPayments(): void
    {
        $location = factory(Location::class)->create();

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
            $unpaidInvoicesCount = 2,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;
        $totalQuantity     = 3;

        $invoiceId1 = $invoices->first()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $invoiceId2 = $invoices->last()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $paymentAmount = ($unitCost - ($unitCost * ($discountInPercent / 100))) * $totalQuantity;
        /** @var Payment $payment1 */
        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        /** @var Payment $payment2 */
        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount,
        ]);

        factory(InvoicePayment::class)->create([
            'payment_id' => $payment2->id,
            'invoice_id' => $invoiceId2,
            'amount'     => $payment2->amount,
            'is_fp'      => true,
        ]);

        $forwardedPayment = factory(ForwardedPayment::class)->create([
            'payment_id'           => $payment2->id,
            'remittance_reference' => 'text',
            'transferred_at'       => Carbon::now(),
        ]);

        $forwardedPaymentAmount = $paymentAmount - $this->faker->randomFloat(2, 1, 5);

        $forwardedPaymentInvoice = factory(ForwardedPaymentInvoice::class)->create([
            'forwarded_payment_id' => $forwardedPayment->id,
            'invoice_id'           => $invoiceId2,
            'amount'               => $forwardedPaymentAmount,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount->accountType->name, $reportData['account_types'][0]['name']);

        $reportAccountItems   = $reportData['account_types'][0]['accounts']['items'];
        $totalForwardedAmount = $reportData['total_forwarded_amount'];
        $amountExTax          = $reportAccountItems[0]['amount_ex_tax'];
        $total                = $reportData['total_amount'];

        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $amountExTax));
        self::assertTrue(Decimal::areEquals($forwardedPaymentInvoice->amount, $totalForwardedAmount));
        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount - $totalForwardedAmount, $total));
    }

    /**
     * Creates two invoice with two items and two payments and two gl accounts.
     * Expects that total = sum of payments.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTwoInvoicesTwoPaymentsTwoItemsTwoGLAccounts(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            AccountTypeGroups::REVENUE
        );

        $glAccount2 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            'sales'
        );

        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 2,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        /** @var InvoiceItem $invoiceItem1 */
        $invoiceItem1 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        /** @var InvoiceItem $invoiceItem2 */
        $invoiceItem2 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $invoiceId2 = $invoices->last()->id;
        /** @var InvoiceItem $invoiceItem3 */
        $invoiceItem3 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount2->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        /** @var InvoiceItem $invoiceItem4 */
        $invoiceItem4 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount2->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $amount   = $invoiceItem1->getSubTotal() + $invoiceItem2->getSubTotal();
        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $amount   = $invoiceItem3->getSubTotal() + $invoiceItem4->getSubTotal();
        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment2->id,
            'invoice_id' => $invoiceId2,
            'amount'     => $payment2->amount,
            'is_fp'      => true,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount1->accountType->name, $reportData['account_types'][0]['name']);
        self::assertEquals($glAccount2->accountType->name, $reportData['account_types'][1]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $total              = $reportAccountItems[0]['amount_ex_tax'];

        $reportAccountItems = $reportData['account_types'][1]['accounts']['items'];
        $total              += $reportAccountItems[0]['amount_ex_tax'];

        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $total));
    }

    /**
     * Creates two invoice with two items and two payments and two different gl accounts for each invoice items.
     * Expects that total = sum of payments.
     *
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testTwoInvoicesTwoPaymentsTwoItemsTwoDifferentGLAccountsForItems(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            AccountTypeGroups::REVENUE
        );

        $glAccount2 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup,
            'sales'
        );

        /** @var Invoice[] $invoice */
        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 2,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        /** @var InvoiceItem $invoiceItem1 */
        $invoiceItem1 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        /** @var InvoiceItem $invoiceItem2 */
        $invoiceItem2 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount2->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $invoiceId2 = $invoices->last()->id;
        /** @var InvoiceItem $invoiceItem3 */
        $invoiceItem3 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        /** @var InvoiceItem $invoiceItem4 */
        $invoiceItem4 = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId2,
                'gl_account_id' => $glAccount2->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 2,
            ]);

        $amount   = $invoiceItem1->getSubTotal() + $invoiceItem2->getSubTotal();
        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $amount   = $invoiceItem3->getSubTotal() + $invoiceItem4->getSubTotal();
        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment2->id,
            'invoice_id' => $invoiceId2,
            'amount'     => $payment2->amount,
            'is_fp'      => true,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
        self::assertEquals($glAccount1->accountType->name, $reportData['account_types'][0]['name']);
        self::assertEquals($glAccount2->accountType->name, $reportData['account_types'][1]['name']);

        $reportAccountItems = $reportData['account_types'][0]['accounts']['items'];
        $total              = $reportAccountItems[0]['amount_ex_tax'];

        $reportAccountItems = $reportData['account_types'][1]['accounts']['items'];
        $total              += $reportAccountItems[0]['amount_ex_tax'];

        self::assertTrue(Decimal::areEquals($payment1->amount + $payment2->amount, $total));
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testEmptyDataIfNotPaidInFull(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
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

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => 89.99,
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter(['location_id' => $location->id]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertEmpty($reportData);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testFilterByDateNoInvoicesInDateRange(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
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

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        /** @var $invoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getTotalAmount(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter([
            'location_id' => $location->id,
            'date_from'   => Carbon::now()->subDays(2),
            'date_to'     => Carbon::now()->subDay(),
        ]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertEmpty($reportData);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testFilterByDateHaveInvoicesInDateRange(): void
    {
        $location = factory(Location::class)->create();

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

        $glAccount1 = GLAccountTestFactory::createGLAccountWithBalance(
            $accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $this->faker->randomFloat(2, 100, 200),
            $isBankAccount = false,
            $code = false,
            $accountTypeGroup
        );

        $invoices = InvoicesTestFactory::createInvoices(
            $unpaidInvoicesCount = 1,
            [
                'accounting_organization_id' => $accountingOrganizationLocation->accounting_organization_id,
                'location_id'                => $location->id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $unitCost          = 100;
        $discountInPercent = 10;

        $invoiceId1 = $invoices->first()->id;
        /** @var $invoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)
            ->create([
                'invoice_id'    => $invoiceId1,
                'gl_account_id' => $glAccount1->id,
                'unit_cost'     => $unitCost,
                'discount'      => $discountInPercent,
                'quantity'      => 1,
            ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $invoiceItem->getTotalAmount(),
        ]);

        InvoicePayment::create([
            'payment_id' => $payment1->id,
            'invoice_id' => $invoices->first()->id,
            'amount'     => $payment1->amount,
            'is_fp'      => false,
        ]);

        $filter = new IncomeReportFilter([
            'location_id' => $location->id,
            'date_from'   => Carbon::now()->subDay(),
            'date_to'     => Carbon::now()->addDay(),
        ]);

        $reportData = $this->service->getIncomeReportData($filter);

        self::assertNotEmpty($reportData);
    }
}
