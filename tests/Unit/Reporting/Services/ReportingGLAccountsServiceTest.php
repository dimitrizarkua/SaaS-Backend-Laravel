<?php

namespace Tests\Unit\Reporting\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Locations\Models\Location;
use App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface;
use App\Components\Reporting\Models\VO\GLAccountTrialReportFilterData;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class ReportingGLAccountsServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   gl-accounts
 * @group   finance
 * @group   reporting
 */
class ReportingGLAccountsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $models       = [
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountTypeGroup::class,
            AccountingOrganization::class,
            AccountingOrganizationLocation::class,
            Contact::class,
            InvoiceItem::class,
            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
            GLAccount::class,
            GSCode::class,
            Location::class,
            Transaction::class,
            TransactionRecord::class,
        ];
        $this->models = array_merge($models, $this->models);

        $this->service = Container::getInstance()
            ->make(ReportingGLAccountServiceInterface::class);
    }

    /**
     * @throws \Throwable
     * @throws \JsonMapper_Exception
     */
    public function testTrialReportSuccess()
    {
        $faker = $this->faker;

        $dateFrom = DateHelper::getFinancialYearStart()
            ->startOfDay();

        $dateTo = (Carbon::today()
            ->subMonth())
            ->addDay()
            ->endOfDay();

        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $reportAccountGroups = collect();
        $reportAccountGroups->push(factory(AccountTypeGroup::class)->create(['name' => AccountTypeGroups::REVENUE]));
        $reportAccountGroups->push(factory(AccountTypeGroup::class)->create(['name' => AccountTypeGroups::ASSET]));
        $reportAccountGroups->push(factory(AccountTypeGroup::class)->create(['name' => AccountTypeGroups::LIABILITY]));

        $otherAccountGroup = factory(AccountTypeGroup::class)->create(['name' => AccountTypeGroups::EQUITY]);

        $reportAccountTypes = collect();
        foreach ($reportAccountGroups as $accountTypeGroup) {
            $reportAccountTypes = $reportAccountTypes->merge(factory(AccountType::class, $faker->numberBetween(2, 3))
                ->create([
                    'increase_action_is_debit' => false,
                    'account_type_group_id'    => $accountTypeGroup->id,
                ]));
        }

        $otherAccountType = factory(AccountType::class)
            ->create([
                'increase_action_is_debit' => false,
                'account_type_group_id'    => $otherAccountGroup->id,
            ]);

        $reportGlAccounts = collect();
        foreach ($reportAccountTypes as $accountType) {
            $reportGlAccounts->push(factory(GLAccount::class)->create([
                'is_active'                  => true,
                'account_type_id'            => $accountType->id,
                'accounting_organization_id' => $organization->id,
            ]));
        }

        $otherGlAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $otherAccountType->id,
            'accounting_organization_id' => $organization->id,
        ]);

        $locationId = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ])->location_id;

        $transactions = collect();

        $transactions->push(factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => $dateTo,
        ]));

        $transactions->push(factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => $dateFrom,
        ]));

        for ($i = 0; $i < $faker->numberBetween(30, 50); $i++) {
            $date = $faker->dateTimeBetween('-1 year', 'now');
            $transactions->push(factory(Transaction::class)->create([
                'accounting_organization_id' => $organization->id,
                'created_at'                 => $date,
            ]));
        }

        $reportTransactionRecords = collect();
        for ($i = 0; $i < $faker->numberBetween(10, 10); $i++) {
            $transactionId = $faker->randomElement($transactions->pluck('id')->toArray());
            $glAccountId   = $faker->randomElement($reportGlAccounts->pluck('id')->toArray());
            $isDebit       = $faker->boolean;
            $reportTransactionRecords->push(factory(TransactionRecord::class)->create([
                'gl_account_id'  => $glAccountId,
                'transaction_id' => $transactionId,
                'is_debit'       => $isDebit,
                'amount'         => $faker->numberBetween(20, 100),
            ]));
        }

        $otherTransactionRecords = collect();
        for ($i = 0; $i < $faker->numberBetween(3, 5); $i++) {
            $transactionId = $faker->randomElement($transactions->pluck('id')->toArray());
            $isDebit       = $faker->boolean;
            $otherTransactionRecords->push(factory(TransactionRecord::class)->create([
                'gl_account_id'  => $otherGlAccount->id,
                'transaction_id' => $transactionId,
                'is_debit'       => $isDebit,
                'amount'         => $faker->numberBetween(20, 100),
            ]));
        }

        $filter = new GlAccountTrialReportFilterData([
            'location_id' => $locationId,
            'date_to'     => $dateTo->format('Y-m-d'),
        ]);

        $responseData = $this->service->getGlAccountTrialReport($filter)
            ->toArray();

        $transactionsFiltered = $transactions
            ->filter(function ($value) use ($dateTo) {
                return $value->created_at->lte($dateTo);
            })
            ->pluck('id')
            ->toArray();

        $transactionsYTD = $transactions
            ->filter(function ($value) use ($dateFrom, $dateTo) {
                return $value->created_at->gte($dateFrom)
                    && $value->created_at->lte($dateTo);
            })
            ->pluck('id')
            ->toArray();

        $totalGroup = $responseData['TOTAL'][0];
        unset($responseData['TOTAL']);

        self::assertEquals(
            $reportTransactionRecords->where('is_debit', true)
                ->whereIn('transaction_id', $transactionsFiltered)->sum('amount'),
            $totalGroup['debit_amount']
        );
        self::assertEquals(
            $reportTransactionRecords->where('is_debit', false)
                ->whereIn('transaction_id', $transactionsFiltered)->sum('amount'),
            $totalGroup['credit_amount']
        );
        self::assertEquals(
            $reportTransactionRecords->where('is_debit', true)
                ->whereIn('transaction_id', $transactionsYTD)->sum('amount'),
            $totalGroup['debit_amount_ytd']
        );
        self::assertEquals(
            $reportTransactionRecords->where('is_debit', false)
                ->whereIn('transaction_id', $transactionsYTD)->sum('amount'),
            $totalGroup['credit_amount_ytd']
        );

        foreach ($responseData as $groupName => $responseGlAccounts) {
            foreach ($responseGlAccounts as $responseGlAccount) {
                $glAccountId = $responseGlAccount['gl_account_id'];
                self::assertEquals(
                    $reportTransactionRecords->where('gl_account_id', $glAccountId)->where('is_debit', true)
                        ->whereIn('transaction_id', $transactionsFiltered)->sum('amount'),
                    $responseGlAccount['debit_amount']
                );
                self::assertEquals(
                    $reportTransactionRecords->where('gl_account_id', $glAccountId)->where('is_debit', false)
                        ->whereIn('transaction_id', $transactionsFiltered)->sum('amount'),
                    $responseGlAccount['credit_amount']
                );
                self::assertEquals(
                    $reportTransactionRecords->where('gl_account_id', $glAccountId)->where('is_debit', true)
                        ->whereIn('transaction_id', $transactionsYTD)->sum('amount'),
                    $responseGlAccount['debit_amount_ytd']
                );
                self::assertEquals(
                    $reportTransactionRecords->where('gl_account_id', $glAccountId)->where('is_debit', false)
                        ->whereIn('transaction_id', $transactionsYTD)->sum('amount'),
                    $responseGlAccount['credit_amount_ytd']
                );
            }
        }

        self::assertEquals(3, count($responseData));
    }
}
