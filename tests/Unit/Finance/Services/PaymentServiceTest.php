<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PaymentsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\CreatePaymentData;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class PaymentServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   finance
 */
class PaymentServiceTest extends TestCase
{
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

    /**
     * @var GLAccount
     */
    private $receivableAccount;

    /**
     * @var GLAccount
     */
    private $salesAccount;

    /**
     * @var GLAccount
     */
    private $taxAccount;

    /**
     * @var \App\Components\Finance\Interfaces\PaymentsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
            TransactionRecord::class,
            Transaction::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Payment::class,
        ], $this->models);

        $assetsAccountType = factory(AccountType::class)->create([
            'name'                     => 'Asset',
            'increase_action_is_debit' => true,
        ]);

        $revenueAccountType = factory(AccountType::class)->create([
            'name'                     => 'Revenue',
            'increase_action_is_debit' => false,
        ]);

        $liabilityAccountType = factory(AccountType::class)->create([
            'name'                     => 'Liability',
            'increase_action_is_debit' => false,
        ]);

        $this->accountingOrganization = factory(AccountingOrganization::class)->create();
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
        $this->receivableAccount      = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
        ]);
        $this->salesAccount           = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $revenueAccountType->id,
        ]);
        $this->taxAccount             = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $liabilityAccountType->id,
        ]);

        $this->service = $this->app->make(PaymentsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateDirectDepositPayment()
    {
        $amount = $this->faker->randomFloat(2, 10, 1000);
        $data   = new CreatePaymentData([
            'amount'                   => $amount,
            'paidAt'                   => Carbon::now(),
            'reference'                => $this->faker->word,
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
        ]);

        $payment = $this->service->createDirectDepositPayment($data);

        self::assertInstanceOf(Payment::class, $payment);
        self::assertEquals($data->getAmount(), $payment->amount);
        self::assertEquals($data->getPaidAt(), $payment->paid_at);
        self::assertNotNull($payment->transaction_id);

        $transaction = Transaction::findOrFail($payment->transaction_id);
        self::assertEquals($this->accountingOrganization->id, $transaction->accounting_organization_id);
        foreach ($transaction->records as $record) {
            self::assertEquals($data->getAmount(), $record->amount);
        }
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateDirectDepositWithMultipleReceivableAccounts()
    {
        $secondAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $this->machinesAccount->account_type_id,
            'enable_payments_to_account' => true,
        ]);
        $thirdAccount  = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $this->machinesAccount->account_type_id,
            'enable_payments_to_account' => true,
        ]);

        $data = new CreatePaymentData([
            'amount'                   => 300,
            'paidAt'                   => Carbon::now(),
            'reference'                => $this->faker->word,
            'accountingOrganizationId' => $this->accountingOrganization->id,
            'payableGLAccountList'     => [
                [
                    'glAccount' => $this->bankAccount,
                    'amount'    => 300,
                ],
            ],
            'receivableGLAccountList'  => [
                [
                    'glAccount' => $this->machinesAccount,
                    'amount'    => 100,
                ],
                [
                    'glAccount' => $secondAccount,
                    'amount'    => 100,
                ],
                [
                    'glAccount' => $thirdAccount,
                    'amount'    => 100,
                ],
            ],
        ]);

        $payment     = $this->service->createDirectDepositPayment($data);
        $transaction = Transaction::findOrFail($payment->transaction_id);

        $records = $transaction->records;
        self::assertCount(4, $records);

        /** @var TransactionRecord $firstRecord */
        $firstRecord = $records->firstWhere('gl_account_id', '=', $this->bankAccount->id);
        self::assertFalse($firstRecord->is_debit);
        self::assertEquals(300, $firstRecord->amount);

        /** @var TransactionRecord $secondRecord */
        $secondRecord = $records->firstWhere('gl_account_id', '=', $secondAccount->id);
        self::assertTrue($secondRecord->is_debit);
        self::assertEquals(100, $secondRecord->amount);

        /** @var TransactionRecord $thirdRecord */
        $thirdRecord = $records->firstWhere('gl_account_id', '=', $thirdAccount->id);
        self::assertTrue($thirdRecord->is_debit);
        self::assertEquals(100, $thirdRecord->amount);

        /** @var TransactionRecord $thirdRecord */
        $fourthRecord = $records->firstWhere('gl_account_id', '=', $this->machinesAccount->id);
        self::assertTrue($fourthRecord->is_debit);
        self::assertEquals(100, $fourthRecord->amount);
    }

    /**
     * @throws \JsonMapper_Exception
     * @group   credit-note
     */
    public function testCreateCreditNotePaymentWithMultiplePayableAccounts()
    {
        $data = new CreatePaymentData([
            'amount'                   => 300,
            'paidAt'                   => Carbon::now(),
            'reference'                => $this->faker->word,
            'accountingOrganizationId' => $this->accountingOrganization->id,
            'payableGLAccountList'     => [
                [
                    'glAccount' => $this->receivableAccount,
                    'amount'    => 300,
                ],
                [
                    'glAccount' => $this->salesAccount,
                    'amount'    => 200,
                ],
                [
                    'glAccount' => $this->taxAccount,
                    'amount'    => 100,
                ],
            ],
        ]);

        $payment = $this->service->createCreditNotePayment($data);

        self::assertInstanceOf(Payment::class, $payment);
        self::assertEquals($data->getAmount(), $payment->amount);
        self::assertEquals($data->getPaidAt(), $payment->paid_at);
        self::assertNotNull($payment->transaction_id);

        $transaction = Transaction::findOrFail($payment->transaction_id);

        $records = $transaction->records;
        self::assertCount(3, $records);

        /** @var TransactionRecord $firstRecord */
        $firstRecord = $records->firstWhere('gl_account_id', '=', $this->receivableAccount->id);
        self::assertFalse($firstRecord->is_debit);
        self::assertEquals(300, $firstRecord->amount);

        /** @var TransactionRecord $secondRecord */
        $secondRecord = $records->firstWhere('gl_account_id', '=', $this->salesAccount->id);
        self::assertTrue($secondRecord->is_debit);
        self::assertEquals(200, $secondRecord->amount);

        /** @var TransactionRecord $thirdRecord */
        $thirdRecord = $records->firstWhere('gl_account_id', '=', $this->taxAccount->id);
        self::assertTrue($thirdRecord->is_debit);
        self::assertEquals(100, $thirdRecord->amount);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToMakePaymentToAccountWithDisabledPaymentsFlag()
    {
        $accountWithDisabledPayments  = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $this->machinesAccount->account_type_id,
            'enable_payments_to_account' => false,
        ]);

        $amount = $this->faker->randomFloat(2, 10, 1000);
        $data   = new CreatePaymentData([
            'amount'                   => $amount,
            'paidAt'                   => Carbon::now(),
            'reference'                => $this->faker->word,
            'accountingOrganizationId' => $this->accountingOrganization->id,
            'payableGLAccountList'     => [
                [
                    'glAccount' => $this->bankAccount,
                    'amount'    => $amount,
                ],
            ],
            'receivableGLAccountList'  => [
                [
                    'glAccount' => $accountWithDisabledPayments,
                    'amount'    => $amount,
                ],
            ],
        ]);

        self::expectException(NotAllowedException::class);

        $this->service->createDirectDepositPayment($data);
    }
}
