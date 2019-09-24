<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\ForwardedPaymentData;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Unit\Finance\GLAccountTestFactory;

/**
 * Class ForwardedPaymentServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   finance
 * @group   forwarded-payments
 */
class ForwardedPaymentServiceTest extends TestCase
{
    /**
     * @var AccountingOrganizationLocation
     */
    private $accountingOrganizationLocation;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var \App\Components\Finance\Services\ForwardedPaymentsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = Container::getInstance()->make(ForwardedPaymentsServiceInterface::class);
        $this->models  = array_merge([
            ForwardedPayment::class,
            ForwardedPaymentInvoice::class,
            Payment::class,
            TaxRate::class,
            Invoice::class,
            GLAccount::class,
            AccountType::class,
            AccountingOrganization::class,
        ], $this->models);

        /** @var User $user */
        $this->userId = factory(User::class)->create()->id;

        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create([
            'user_id' => $this->userId,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation */
        $this->accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)->create([
            'location_id' => $locationUser->location_id,
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testForwardSuccess()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 1, 10);

        $invoiceItemCount = $this->faker->numberBetween(1, 3);
        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->userId,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        $invoicePayments = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $amount,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;
        $balance             = $invoiceItemCount * $amount;

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayments->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->userId);

        $payment = $this->service->forward($forwardedPaymentData);

        Payment::findOrFail($payment->id);
        $transaction = Transaction::findOrFail($payment->transaction_id);

        /** Debit transaction record*/
        TransactionRecord::query()
            ->where([
                'transaction_id' => $transaction->id,
                'gl_account_id'  => $glAccountSrc->id,
                'amount'         => $amount,
                'is_debit'       => false,
            ])->firstOrFail();

        /** Credit transaction record */
        TransactionRecord::query()
            ->where([
                'transaction_id' => $transaction->id,
                'gl_account_id'  => $glAccountDst->id,
                'amount'         => $amount,
                'is_debit'       => true,
            ])->firstOrFail();

        $forwardedPayment = ForwardedPayment::query()
            ->where('payment_id', $payment->id)
            ->get();

        $forwardedPaymentInvoices = ForwardedPaymentInvoice::query()
            ->whereIn('invoice_id', $fpInvoicesIds)
            ->get();

        self::assertEquals(1, $forwardedPayment->count());
        self::assertEquals($payment->id, $forwardedPayment[0]->payment_id);
        self::assertEquals($invoice->id, $forwardedPaymentInvoices[0]->invoice_id);
        self::assertNotNull($forwardedPayment[0]->transferred_at);
        self::assertNotNull($forwardedPayment[0]->remittance_reference);

        $forwardedPaymentInvoiceTotalAmount = 0;
        foreach ($forwardedPaymentInvoices as $forwardedPaymentInvoice) {
            $forwardedPaymentInvoiceTotalAmount += $forwardedPaymentInvoice->amount;
        }

        self::assertEquals(bccomp($payment->amount, $forwardedPaymentInvoiceTotalAmount, 2), 0);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToForwardBecauseOfSourceAccountIsNotBank()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount           = $this->faker->randomFloat(2, 1, 10);
        $invoiceItemCount = $this->faker->numberBetween(1, 3);

        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->userId,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        $invoicePayments = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $amount,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;

        $balance = $invoiceItemCount * $amount;

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance,
            $isBankAccount = false
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayments->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->userId);

        self::expectException(NotAllowedException::class);
        self::expectExceptionMessage(
            sprintf('Source account [%s] must be a bank account.', $glAccountSrc->name)
        );
        $this->service->forward($forwardedPaymentData);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToForwardBecauseOfFundsNotEnough()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount           = $this->faker->randomFloat(2, 5, 10);
        $invoiceItemCount = $this->faker->numberBetween(1, 3);

        factory(InvoiceItem::class, $invoiceItemCount)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->userId,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $amount,
        ]);

        $invoicePayments = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $amount,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;

        $balanceLessThanFunds = $amount - $this->faker->randomFloat(2, 1, 4);
        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balanceLessThanFunds,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayments->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->userId);

        self::expectException(NotAllowedException::class);
        self::expectExceptionMessage(sprintf('Source account [%s] does not have enough funds.', $glAccountSrc->name));
        $this->service->forward($forwardedPaymentData);
    }


    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToForwardBecauseOfFundsIsZero()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $funds = 0;

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->userId,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);

        $payment = factory(Payment::class)->create([
            'type'   => PaymentTypes::DIRECT_DEPOSIT,
            'amount' => $funds,
        ]);

        $invoicePayments = factory(InvoicePayment::class)->create([
            'payment_id' => $payment->id,
            'amount'     => $funds,
            'invoice_id' => $invoice->id,
            'is_fp'      => true,
        ]);

        $remittanceReference = $this->faker->text;
        $balance             = $this->faker->randomFloat(2, 5, 10);
        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance,
            $isBankAccount = true
        );

        $glAccountDst = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance,
            $isBankAccount = false
        );

        $today = Carbon::now();

        $fpInvoicesIds = [$invoicePayments->invoice_id];

        $forwardedPaymentData = new ForwardedPaymentData();
        $forwardedPaymentData->setSourceGLAccountId($glAccountSrc->id)
            ->setDestinationGLAccountId($glAccountDst->id)
            ->setTransferredAt($today)
            ->setRemittanceReference($remittanceReference)
            ->setInvoicesIds($fpInvoicesIds)
            ->setUserId($this->userId);

        self::expectException(NotAllowedException::class);
        self::expectExceptionMessage('Incorrect funds value. It is less or equals to zero.');
        $this->service->forward($forwardedPaymentData);
    }
}
