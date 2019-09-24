<?php

namespace Tests\API\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
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
use Carbon\Carbon;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\GLAccountTestFactory;

/**
 * Class ForwardedPaymentsControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 */
class ForwardedPaymentsControllerTest extends ApiTestCase
{
    public $permissions = ['finance.payments.forward'];

    /** @var \App\Components\Finance\Models\AccountingOrganizationLocation */
    private $accountingOrganizationLocation;

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
            ForwardedPayment::class,
            ForwardedPaymentInvoice::class,
            AccountingOrganizationLocation::class,
        ], $this->models);

        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create([
            'user_id' => $this->user->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation */
        $this->accountingOrganizationLocation = factory(AccountingOrganizationLocation::class)->create([
            'location_id' => $locationUser->location_id,
        ]);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testForwardSimpleSuccess()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 1, 10);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->user->id,
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

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 120.00,
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
            ->setUserId($this->user->id);

        $url = action('Finance\ForwardedPaymentsController@forward');

        $data = [
            'invoices_ids'              => $fpInvoicesIds,
            'transferred_at'            => $today,
            'location_id'               => $this->accountingOrganizationLocation->location_id,
            'remittance_reference'      => $remittanceReference,
            'source_gl_account_id'      => $glAccountSrc->id,
            'destination_gl_account_id' => $glAccountDst->id,
        ];

        $this->postJson($url, $data)
            ->assertStatus(200);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToForwardInsufficientFundsOnSrcGLAccount()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 1, 10);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->user->id,
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

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 0,
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
            ->setUserId($this->user->id);

        $url = action('Finance\ForwardedPaymentsController@forward');

        $data = [
            'invoices_ids'              => $invoicePayments->pluck('invoice_id'),
            'transferred_at'            => $today,
            'location_id'               => $this->accountingOrganizationLocation->location_id,
            'remittance_reference'      => $remittanceReference,
            'source_gl_account_id'      => $glAccountSrc->id,
            'destination_gl_account_id' => $glAccountDst->id,
        ];

        $this->postJson($url, $data)
            ->assertNotAllowed(sprintf('Source account [%s] does not have enough funds.', $glAccountSrc->name));
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToForwardSourceAccountIsNotBankAccount()
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id'                => $this->accountingOrganizationLocation->location_id,
            'accounting_organization_id' => $this->accountingOrganizationLocation->accounting_organization_id,
        ]);

        $amount = $this->faker->randomFloat(2, 1, 10);

        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'user_id'    => $this->user->id,
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

        /** @var GLAccount $glAccount */
        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganizationLocation->accounting_organization_id,
            $increaseActionIsDebit = true,
            $balance = 120.33,
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
            ->setUserId($this->user->id);

        $url = action('Finance\ForwardedPaymentsController@forward');

        $data = [
            'invoices_ids'              => $invoicePayments->pluck('invoice_id'),
            'transferred_at'            => $today,
            'location_id'               => $this->accountingOrganizationLocation->location_id,
            'remittance_reference'      => $remittanceReference,
            'source_gl_account_id'      => $glAccountSrc->id,
            'destination_gl_account_id' => $glAccountDst->id,
        ];

        $this->postJson($url, $data)
            ->assertNotAllowed(
                sprintf('Source account [%s] must be a bank account.', $glAccountSrc->name)
            );
    }
}
