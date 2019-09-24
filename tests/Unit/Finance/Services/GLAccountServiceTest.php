<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class GLAccountServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   services
 * @group   finance
 */
class GLAccountServiceTest extends TestCase
{
    /**
     * @var \App\Components\Finance\Interfaces\GLAccountServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
            TransactionRecord::class,
            Transaction::class,
            GLAccount::class,
            AccountType::class,
            AccountingOrganization::class,
            Payment::class,
        ], $this->models);

        $this->service = Container::getInstance()
            ->make(GLAccountServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetEmptyResultSetTransactionRecordsByAccount()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = new GLAccountTransactionFilter();

        $transactionRecordsByAccount = $this->service->findTransactionRecordsByAccount($glAccount->id, $filter);

        self::assertEmpty($transactionRecordsByAccount->get());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetTransactionRecordsByAccount()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => true,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt = $this->faker->numberBetween(1, 5);
        factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $filter = new GLAccountTransactionFilter();

        $transactionRecordsByAccount = $this->service->findTransactionRecordsByAccount($glAccount->id, $filter);

        $transactionRecordsByAccountIds = $transactionRecordsByAccount->pluck('id')->toArray();
        self::assertNotEmpty($transactionRecordsByAccount);

        foreach ($transactionRecordsByAccount as $record) {
            self::assertTrue(in_array($record->id, $transactionRecordsByAccountIds));
        }
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetTransactionRecordsByAccountWithFilter()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt = $this->faker->numberBetween(1, 5);
        factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $filter = new GLAccountTransactionFilter();
        $filter->setDateFrom((new Carbon())->subDay());
        $filter->setDateTo((new Carbon())->addDay());

        $transactionRecordsByAccount = $this->service->findTransactionRecordsByAccount($glAccount->id, $filter);
        self::assertNotEmpty($transactionRecordsByAccount);

        foreach ($transactionRecordsByAccount as $record) {
            self::assertTrue(in_array($record->id, $transactionRecordsByAccount->pluck('id')->toArray()));
        }
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetTransactionRecordsByAccountWithFilterAndOtherTransactions()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt = $this->faker->numberBetween(1, 5);
        factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        // previous transactions - start
        factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => (new Carbon())->subDay(),
        ]);

        $cnt = $this->faker->numberBetween(1, 5);
        factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);
        // previous transactions - end

        $filter = new GLAccountTransactionFilter();
        $filter->setDateFrom(new Carbon());
        $filter->setDateTo((new Carbon())->addDay());

        $transactionRecordsByAccount = $this->service->findTransactionRecordsByAccount($glAccount->id, $filter);
        self::assertNotEmpty($transactionRecordsByAccount);

        foreach ($transactionRecordsByAccount as $record) {
            self::assertTrue(in_array($record->id, $transactionRecordsByAccount->pluck('id')->toArray()));
        }
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetZeroBalanceByAccountIfNoTransactions()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = new GLAccountTransactionFilter();
        $filter->setDateFrom(new Carbon());
        $filter->setDateTo((new Carbon())->addDay());

        $balance = $this->service->getAccountBalance($glAccount->id, $filter);
        self::assertEquals(bccomp("0.00", $balance, 2), 0);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetBalanceByAccount()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $accountType = factory(AccountType::class)->create([
            'increase_action_is_debit' => false,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => new Carbon(),
        ]);

        $cnt                = $this->faker->numberBetween(1, 5);
        $transactionRecords = factory(TransactionRecord::class, $cnt)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);

        $today  = new Carbon();
        $filter = new GLAccountTransactionFilter();
        $filter->setDateFrom($today);
        $filter->setDateTo($today->addDay());

        $calculatedBalance = 0;
        foreach ($transactionRecords as $rec) {
            /** TransactionRecord $record */
            $calculatedBalance = bcadd($calculatedBalance, $rec->amount, 2);
        }

        $balance = $this->service->getAccountBalance($glAccount->id, $filter);
        self::assertEquals(bccomp($calculatedBalance, $balance, 2), 0);
    }
}
