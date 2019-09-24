<?php

namespace Tests\Unit\Finance\Domains;

use App\Components\Finance\Domains\FinancialTransaction;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Tests\TestCase;

/**
 * Class FinancialTransactionTest
 *
 * @package Tests\Unit\Finance\Domains
 * @group   finance
 */
class FinancialTransactionTest extends TestCase
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
        ], $this->models);


        $assetsAccountType = factory(AccountType::class)->create([
            'name'                     => 'Asset',
            'increase_action_is_debit' => true,
        ]);

        $this->accountingOrganization = factory(AccountingOrganization::class)->create();
        $this->bankAccount            = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
        ]);
        $this->machinesAccount        = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
        ]);
    }

    public function testDomainWorkFlow()
    {
        $amount = $this->faker->randomFloat(2, 10, 1000);

        $transactionId = FinancialTransaction::make($this->accountingOrganization->id)
            ->decrease($this->bankAccount, $amount)
            ->increase($this->machinesAccount, $amount)
            ->commit();

        $result = Transaction::find($transactionId);
        self::assertNotNull(1, $result);
        self::assertCount(2, $result->records);

        $firstRecord = TransactionRecord::where('transaction_id', $transactionId)
            ->where('gl_account_id', $this->bankAccount->id)
            ->first();
        self::assertNotNull($firstRecord);
        self::assertEquals($amount, $firstRecord->amount);
        self::assertFalse($firstRecord->is_debit);

        $secondRecord = TransactionRecord::where('transaction_id', $transactionId)
            ->where('gl_account_id', $this->machinesAccount->id)
            ->first();
        self::assertNotNull($secondRecord);
        self::assertEquals($amount, $secondRecord->amount);
        self::assertTrue($secondRecord->is_debit);
    }

    public function testExceptionShouldBeThrownIfActionsOfAccountTypeAreDifferent()
    {
        $this->machinesAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                //This flag is different
                'increase_action_is_debit' => false,
            ])->id,
        ]);

        self::expectException(NotAllowedException::class);
        FinancialTransaction::make($this->accountingOrganization->id)
            ->decrease($this->bankAccount, 10)
            ->increase($this->machinesAccount, 10)
            ->commit();
    }

    public function testExceptionShouldBeThrownIfGlAccountsBelongsToDiffernetAccountingOrganizations()
    {
        $anotherAccountingOrganization = factory(AccountingOrganization::class)->create();
        $this->machinesAccount         = factory(GLAccount::class)->create([
            'accounting_organization_id' => $anotherAccountingOrganization->id,
            'account_type_id'            => $this->bankAccount->account_type_id,
        ]);

        self::expectException(NotAllowedException::class);
        FinancialTransaction::make($this->accountingOrganization->id)
            ->decrease($this->bankAccount, 10)
            ->increase($this->machinesAccount, 10)
            ->commit();
    }

    public function testNotAllowedToCommitOneTransactionTwice()
    {
        $transaction = FinancialTransaction::make($this->accountingOrganization->id)
            ->decrease($this->bankAccount, 10)
            ->increase($this->machinesAccount, 10);

        $transaction->commit();

        self::expectException(NotAllowedException::class);
        $transaction
            ->decrease($this->bankAccount, 10)
            ->increase($this->machinesAccount, 10)
            ->commit();
    }
}
