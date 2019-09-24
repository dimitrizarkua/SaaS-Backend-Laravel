<?php

namespace App\Jobs\Finance;

use App\Components\Finance\Enums\GLAccountStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class CreateDefaultGLAccounts
 *
 * @package App\Jobs\Finance
 */
class CreateDefaultGLAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TAX_PAYABLE_ACCOUNT_CODE         = '821';
    private const TAX_RECEIVABLE_ACCOUNT_CODE      = '822';
    private const ACCOUNTS_PAYABLE_ACCOUNT_CODE    = '800';
    private const ACCOUNTS_RECEIVABLE_ACCOUNT_CODE = '610';
    private const PAYMENT_DETAILS_ACCOUNT_CODE     = '100010';

    /** @var int $accountingOrganizationId */
    private $accountingOrganizationId;

    private static $specialAccountColumns = [
        self::TAX_PAYABLE_ACCOUNT_CODE         => 'tax_payable_account_id',
        self::TAX_RECEIVABLE_ACCOUNT_CODE      => 'tax_receivable_account_id',
        self::ACCOUNTS_PAYABLE_ACCOUNT_CODE    => 'accounts_payable_account_id',
        self::ACCOUNTS_RECEIVABLE_ACCOUNT_CODE => 'accounts_receivable_account_id',
        self::PAYMENT_DETAILS_ACCOUNT_CODE     => 'payment_details_account_id',
    ];

    private static $accountTypeNames = [
        'Revenue',
        'Direct Cost of Sales',
        'Bank',
        'Current Asset',
        'Current Liability',
    ];

    private static $taxRateNames = [
        'GST on Income',
        'GST on Expenses',
        'BAS Excluded',
    ];

    private static $accounts = [
        [
            'code'                       => '301',
            'export_code'                => '310000',
            'name'                       => 'Sales - Fire',
            'description'                => 'All Fire related jobs',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '302',
            'export_code'                => '310100',
            'name'                       => 'Sales - Water',
            'description'                => 'All Water related jobs',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '303',
            'export_code'                => '310200',
            'name'                       => 'Sales - General Cleaning',
            'description'                => 'All general cleaning related jobs',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '304',
            'export_code'                => '310300',
            'name'                       => 'Sales - Specialist Cleaning',
            'description'                => 'All specialist cleaning related jobs',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '305',
            'export_code'                => '311000',
            'name'                       => 'Sales - Mould',
            'description'                => 'All mould related jobs',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '306',
            'export_code'                => '390000',
            'name'                       => 'Work in Progress (EOY)',
            'description'                => 'End of year work in-progress which you may wish to account for as revenue',
            'account_type_name_id'       => 0,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 0,
            'is_special'                 => false,
        ],
        [
            'code'                       => '420',
            'export_code'                => '450001',
            'name'                       => 'LAHA',
            'description'                => 'Living Away from Home Allowance',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '401',
            'export_code'                => '401000',
            'name'                       => 'Materials',
            'description'                => 'Materials and chemicals',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '402',
            'export_code'                => '450000',
            'name'                       => 'General Expenses',
            'description'                => 'Other consumables and services',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '403',
            'export_code'                => '420000',
            'name'                       => 'Equipment Hire',
            'description'                => '',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '404',
            'export_code'                => '402001',
            'name'                       => 'Carpet Layers',
            'description'                => '',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '405',
            'export_code'                => '402300',
            'name'                       => 'Sub Contractors',
            'description'                => '',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '406',
            'export_code'                => '402301',
            'name'                       => 'Waste Removal (Sub-Contractors)',
            'description'                => '',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '440',
            'export_code'                => '410000',
            'name'                       => 'Royalties Payable',
            'description'                => 'Royalities payable to franchises or branches',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => '450',
            'export_code'                => '490001',
            'name'                       => 'Motor Vehicle - Rentals',
            'description'                => '',
            'account_type_name_id'       => 1,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => 1,
            'is_special'                 => false,
        ],
        [
            'code'                       => null,
            'export_code'                => '100010',
            'name'                       => 'Trading Bank Account',
            'description'                => 'Bank Trading Account',
            'account_type_name_id'       => 2,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => false,
        ],
        [
            'code'                       => '610',
            'export_code'                => '100100',
            'name'                       => 'Accounts Receivable',
            'description'                => '',
            'account_type_name_id'       => 3,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => true,
        ],
        [
            'code'                       => '612',
            'export_code'                => '100109',
            'name'                       => 'Clearing Account',
            'description'                => '',
            'account_type_name_id'       => 3,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => false,
        ],
        [
            'code'                       => '614',
            'export_code'                => null,
            'name'                       => 'Franchise Payments (Holding)',
            'description'                => 'Special - temporary holding for forwarding payments to other locations',
            'account_type_name_id'       => 4,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => true,
        ],
        [
            'code'                       => '800',
            'export_code'                => null,
            'name'                       => 'Accounts Payable',
            'description'                => '',
            'account_type_name_id'       => 4,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => true,
        ],
        [
            'code'                       => '820',
            'export_code'                => '200800',
            'name'                       => 'GST',
            'description'                => 'The balance of this account presents the amount' .
                'oweing or payable to the ATO',
            'account_type_name_id'       => 4,
            'enable_payments_to_account' => true,
            'tax_rate_name_id'           => 2,
            'is_special'                 => true,
        ],
        [
            'code'                       => '821',
            'export_code'                => '',
            'name'                       => 'Tax Payable',
            'description'                => '',
            'account_type_name_id'       => 4,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => null,
            'is_special'                 => true,
        ],
        [
            'code'                       => '822',
            'export_code'                => '',
            'name'                       => 'Tax Receivable',
            'description'                => '',
            'account_type_name_id'       => 3,
            'enable_payments_to_account' => false,
            'tax_rate_name_id'           => null,
            'is_special'                 => true,
        ],
    ];

    /**
     * CreateDefaultGLAccounts constructor.
     *
     * @param int $organizationId
     */
    public function __construct(int $organizationId)
    {
        $this->accountingOrganizationId = $organizationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        Log::info(
            sprintf(
                'Creating default GL accounts for organization [ID:%s].',
                $this->accountingOrganizationId
            ),
            ['accounting_organization_id' => $this->accountingOrganizationId]
        );

        DB::transaction(function () {
            foreach (self::$accounts as $account) {
                $accountType = $this->getAccountType($account['account_type_name_id']);
                $taxRateId   = null !== $account['tax_rate_name_id']
                    ? $this->getTaxRate($account['tax_rate_name_id'])->id
                    : null;

                try {
                    $account = GLAccount::create([
                        'accounting_organization_id' => $this->accountingOrganizationId,
                        'code'                       => $account['code'],
                        'export_code'                => $account['export_code'],
                        'name'                       => $account['name'],
                        'description'                => $account['description'],
                        'account_type_id'            => $accountType->id,
                        'enable_payments_to_account' => $account['enable_payments_to_account'],
                        'tax_rate_id'                => $taxRateId,
                        'is_special'                 => $account['is_special'],
                        'status'                     => GLAccountStatuses::ACTIVE,
                    ]);

                    $code = $account['code'] ?? $account['export_code'];

                    if (isset(self::$specialAccountColumns[$code])) {
                        $column = self::$specialAccountColumns[$code];

                        AccountingOrganization::query()
                            ->where('id', '=', $this->accountingOrganizationId)
                            ->update([$column => $account->id]);
                    }
                } catch (\Exception $e) {
                    Log::error(
                        sprintf('GL account not created for organization [ID:%s].', $this->accountingOrganizationId),
                        [
                            'message'                    => $e->getMessage(),
                            'code'                       => $account['code'],
                            'account_type_id'            => $accountType->id,
                            'tax_rate_id'                => $taxRateId,
                            'accounting_organization_id' => $this->accountingOrganizationId,
                        ]
                    );

                    throw $e;
                }
            }
        });

        Log::info(
            sprintf(
                'Default GL accounts successfully created for organization [ID:%s].',
                $this->accountingOrganizationId
            ),
            ['accounting_organization_id' => $this->accountingOrganizationId]
        );
    }

    /**
     * @param int $typeNameId
     *
     * @return \App\Components\Finance\Models\AccountType
     */
    private function getAccountType(int $typeNameId): AccountType
    {
        $accountTypeName = self::$accountTypeNames[$typeNameId];

        try {
            return AccountType::query()
                ->where(['name' => $accountTypeName,])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error(
                sprintf('Account type [name:%s] not found.', $accountTypeName),
                [
                    'name'                       => $accountTypeName,
                    'accounting_organization_id' => $this->accountingOrganizationId,
                ]
            );

            throw $e;
        }
    }

    /**
     * @param int $rateNameId
     *
     * @return \App\Components\Finance\Models\TaxRate
     */
    private function getTaxRate(int $rateNameId): TaxRate
    {
        $taxRateName = self::$taxRateNames[$rateNameId];

        try {
            return TaxRate::query()
                ->where(['name' => $taxRateName,])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error(
                sprintf('Tax rate [name:%s] not found.', $taxRateName),
                [
                    'name'                       => $taxRateName,
                    'accounting_organization_id' => $this->accountingOrganizationId,
                ]
            );

            throw $e;
        }
    }
}
