<?php

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Class ReportingTransactionSeeder
 */
class ReportingTransactionSeeder extends Seeder
{
    /**
     * Seed the contact types, categories and statuses.
     *
     * @return void
     */
    public function run()
    {
        /** @var GLAccount $model */
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $accountType = AccountType::query()
            ->where('name', 'Asset')
            ->first();

        $glAccount = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'account_type_id'            => $accountType->id,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $prevTransaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => (new Carbon('first day of this month'))->subDay(),
        ]);

        $count = 5;
        // previous transactions
        factory(TransactionRecord::class, $count)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $prevTransaction->id,
            'is_debit'       => true,
        ]);

        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $organization->id,
            'created_at'                 => new Carbon(),
        ]);

        // transactions into filter range
        factory(TransactionRecord::class, $count)->create([
            'gl_account_id'  => $glAccount->id,
            'transaction_id' => $transaction->id,
            'is_debit'       => false,
        ]);
    }
}
