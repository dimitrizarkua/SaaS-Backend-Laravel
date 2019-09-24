<?php

namespace Tests\Integration\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\ForwardedPaymentData;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ForwardedPaymentsTest
 *
 * @package Tests\Integration\Finance
 * @group   forwarded-payments
 * @group   finance
 */
class ForwardedPaymentsTest extends TestCase
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var \App\Components\Finance\Interfaces\GLAccountServiceInterface
     */
    private $glAccountService;

    /**
     * @var \App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface
     */
    private $forwardedPaymentService;

    /**
     * @var \App\Components\Finance\Models\AccountingOrganizationLocation
     */
    private $accountingOrganizationLocation;

    public function setUp()
    {
        parent::setUp();

        $user       = factory(User::class)->create();
        $this->user = $user;
        $this->actingAs($user);
        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create([
            'user_id' => $user->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation */
        $this->accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)->create([
            'location_id' => $locationUser->location_id,
        ]);

        $this->glAccountService = Container::getInstance()
            ->make(GLAccountServiceInterface::class);

        $this->forwardedPaymentService = Container::getInstance()
            ->make(ForwardedPaymentsServiceInterface::class);

        $models       = [
            ForwardedPayment::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->forwardedPaymentService, $this->glAccountService);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckBalanceAfterForwarding()
    {
        $countOfInvoices = $this->faker->numberBetween(2, 3);
        $invoices        = InvoicesTestFactory::createInvoices(
            $countOfInvoices,
            [
                'location_id'                => $this->accountingOrganizationLocation->location_id,
                'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoicesIds = $invoices->pluck('id')
            ->toArray();

        InvoicesTestFactory::createPaymentsForInvoices(
            $invoicesIds,
            PaymentTypes::DIRECT_DEPOSIT,
            $minAmount = 10,
            $maxAmount = 20,
            $isFp = true
        );

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($invoicesIds)
            ->setUserId($this->user->id);

        $payment = $this->forwardedPaymentService->forward($forwardedPaymentData);

        $sourceGLAccountBalance = $this->glAccountService->getAccountBalance($glAccountSrc->id);
        self::assertEquals(
            bccomp($sourceGLAccountBalance, $initSourceBalance - $payment->amount, 2),
            0
        );

        $dstGLAccountBalance = $this->glAccountService->getAccountBalance($glAccountDst->id);
        self::assertEquals(
            bccomp($dstGLAccountBalance, $payment->amount, 2),
            0
        );
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckForwardedWasHiddenAfterForward()
    {
        $countOfInvoices = $this->faker->numberBetween(2, 3);
        $invoices        = InvoicesTestFactory::createInvoices(
            $countOfInvoices,
            [
                'location_id'                => $this->accountingOrganizationLocation->location_id,
                'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoicesIds = $invoices->pluck('id')
            ->toArray();

        InvoicesTestFactory::createPaymentsForInvoices(
            $invoicesIds,
            PaymentTypes::DIRECT_DEPOSIT,
            $minAmount = 10,
            $maxAmount = 20,
            $isFp = true
        );

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($invoicesIds)
            ->setUserId($this->user->id);

        $this->forwardedPaymentService->forward($forwardedPaymentData);

        $unforwardedInvoices = $this->forwardedPaymentService->getUnforwarded(
            $this->accountingOrganizationLocation->location_id
        );
        self::assertEmpty($unforwardedInvoices);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckForwardedTwoPartialPaymentWithoutUnforwardedShouldBeProcessed()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 50, 100);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->user->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $paymentAmount1 = $amount - $this->faker->randomFloat(2, 1, 20);
        $payment        = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount1,
        ]);

        $invoicePayment = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $paymentAmount1,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $paymentAmount2 = $amount - $paymentAmount1;
        $payment        = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount2,
        ]);

        /** second invoice payment */
        factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $paymentAmount2,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);
        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayment->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->user->id);

        $payment = $this->forwardedPaymentService->forward($forwardedPaymentData);

        $forwardedPaymentInvoice = ForwardedPaymentInvoice::query()
            ->where('invoice_id', $invoicePayment->invoice_id)
            ->firstOrFail();

        self::assertEquals(
            bccomp($paymentAmount1 + $paymentAmount2, $forwardedPaymentInvoice->amount),
            0
        );

        self::assertEquals(
            bccomp($payment->amount, $forwardedPaymentInvoice->amount),
            0
        );

        $unforwardedInvoicesIds = $this->forwardedPaymentService->getUnforwarded(
            $this->accountingOrganizationLocation->location_id
        );

        self::assertEmpty($unforwardedInvoicesIds);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckSeveralInvoices()
    {
        $countOfInvoices = $this->faker->numberBetween(2, 3);
        $invoices        = InvoicesTestFactory::createInvoices(
            $countOfInvoices,
            [
                'location_id'                => $this->accountingOrganizationLocation->location_id,
                'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
            ],
            FinancialEntityStatuses::APPROVED,
            true
        );

        $invoicesIds = $invoices->pluck('id')
            ->toArray();

        InvoicesTestFactory::createPaymentsForInvoices(
            $invoicesIds,
            PaymentTypes::DIRECT_DEPOSIT,
            $minAmount = 10,
            $maxAmount = 20,
            $isFp = true
        );

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($invoicesIds)
            ->setUserId($this->user->id);

        $payment = $this->forwardedPaymentService->forward($forwardedPaymentData);

        foreach (InvoicePayment::get() as $fpInvoice) {
            ForwardedPaymentInvoice::query()
                ->where('invoice_id', $fpInvoice->invoice_id)
                ->where('amount', $fpInvoice->amount)
                ->firstOrFail();
        }

        $unforwardedInvoicesIds = $this->forwardedPaymentService->getUnforwarded(
            $this->accountingOrganizationLocation->location_id
        );

        self::assertEmpty($unforwardedInvoicesIds);

        $paymentAmount                = $payment->amount;
        $forwardPaymentInvoicesAmount = ForwardedPaymentInvoice::query()->get()->sum('amount');
        self::assertEquals(bccomp($paymentAmount, $forwardPaymentInvoicesAmount, 2), 0);

        $sourceGLAccountBalance = $this->glAccountService->getAccountBalance($glAccountSrc->id);
        self::assertEquals(
            bccomp($sourceGLAccountBalance, $initSourceBalance - $payment->amount, 2),
            0
        );

        $dstGLAccountBalance = $this->glAccountService->getAccountBalance($glAccountDst->id);
        self::assertEquals(
            bccomp($dstGLAccountBalance, $payment->amount, 2),
            0
        );
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckPartialInvoiceThatForwardedBefore()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 50, 100);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->user->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $paymentAmount1 = $amount - $this->faker->randomFloat(2, 1, 20);
        $payment        = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount1,
        ]);

        $invoicePayment = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $paymentAmount1,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $paymentAmount2 = $amount - $paymentAmount1;
        $payment        = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount2,
        ]);

        /** second invoice payment */
        factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $paymentAmount2,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);
        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayment->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->user->id);

        // first forward
        $this->forwardedPaymentService->forward($forwardedPaymentData);

        $paymentAmount3 = 1.33;
        $payment        = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $paymentAmount3,
        ]);

        /** second invoice payment */
        $invoicePayment = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $paymentAmount3,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);


        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayment->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->user->id);

        // second forward
        $this->forwardedPaymentService->forward($forwardedPaymentData);

        self::assertEquals(
            bccomp(
                $paymentAmount1 + $paymentAmount2 + $paymentAmount3,
                ForwardedPaymentInvoice::sum('amount'),
                2
            ),
            0
        );

        $unforwardedInvoicesIds = $this->forwardedPaymentService->getUnforwarded(
            $this->accountingOrganizationLocation->location_id
        );

        self::assertEmpty($unforwardedInvoicesIds);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCheckNotAllInvoiceWillBeHiddenAfterForward()
    {
        /** @var Invoice $invoice1 */
        $invoice1 = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        /** @var Invoice $invoice2 */
        $invoice2 = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 1, 10);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice1->id,
            'user_id'    => $this->user->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice2->id,
            'user_id'    => $this->user->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $payment1 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        $invoicePayment1 = factory(InvoicePayment::class)->create([
            'payment_id' => $payment1->id,
            'amount'     => $amount,
            'invoice_id' => $invoice1->id,
            'is_fp'      => true,
        ]);

        $payment2 = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        $invoicePayment2 = factory(InvoicePayment::class)->create([
            'payment_id' => $payment2->id,
            'amount'     => $amount,
            'invoice_id' => $invoice2->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;

        $initSourceBalance = $this->faker->randomFloat(2, 100, 200);
        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = $initSourceBalance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayment1->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->user->id);

        $this->forwardedPaymentService->forward($forwardedPaymentData);

        $unforwardedInvoices = $this->forwardedPaymentService->getUnforwarded(
            $this->accountingOrganizationLocation->location_id
        );

        self::assertFalse($unforwardedInvoices->isEmpty());
        self::assertEquals($unforwardedInvoices[0]->invoice_id, $invoicePayment2->invoice_id);
    }
}
