<?php

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\GLAccount;
use App\Components\Jobs\Models\Job;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Tests\Unit\Finance\GLAccountTestFactory;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ForwardedPaymentsInvoicesSeeder
 */
class ForwardedPaymentsInvoicesSeeder extends Seeder
{
    /**
     * @throws \Throwable
     */
    public function run()
    {
        foreach (AccountingOrganizationLocation::all() as $aol) {
            $countOfInvoices = 10;

            try {
                $job = Job::query()
                    ->where('assigned_location_id', $aol->location_id)
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                continue;
            }

            $invoices = InvoicesTestFactory::createInvoices(
                $countOfInvoices,
                [
                    'location_id'                => $aol->location_id,
                    'accounting_organization_id' => $aol->accounting_organization_id,
                    'job_id'                     => $job->id,
                ],
                FinancialEntityStatuses::APPROVED,
                true
            );

            InvoicesTestFactory::createPaymentsForInvoices(
                $invoices->pluck('id')->toArray(),
                PaymentTypes::DIRECT_DEPOSIT,
                $minAmount = 10,
                $maxAmount = 20,
                $isFp = true
            );

            $initSourceBalance = 10000;

            /** @var GLAccount $glAccount Source GL Account */
            GLAccountTestFactory::createGLAccountWithBalance(
                $aol->accounting_organization_id,
                $increaseActionIsDebit = true,
                $balance = $initSourceBalance,
                $isBankAccount = true
            );

            /** @var GLAccount $glAccount Destination GL Account */
            GLAccountTestFactory::createGLAccountWithBalance(
                $aol->accounting_organization_id,
                $increaseActionIsDebit = true,
                $balance = 0,
                $isBankAccount = false
            );
        }
    }
}
