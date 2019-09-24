<?php

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use Illuminate\Database\Seeder;

/**
 * Class AccountTypeGroupsSeeder
 */
class AccountTypeGroupsSeeder extends Seeder
{
    private $data = [
        AccountTypeGroups::ASSET     => [
            'increase_action_is_debit' => true,
            'show_on_pl'               => false,
            'show_on_bs'               => true,
            'types'                    => [
                'Bank',
                'Current Asset',
                'Fixed Asset',
                'Non-Current',
                'Prepayment',
                'Bank Accounts',
            ],
        ],
        AccountTypeGroups::EXPENSE   => [
            'increase_action_is_debit' => true,
            'show_on_pl'               => true,
            'show_on_bs'               => false,
            'types'                    => [
                'Direct Cost of Sales',
                'Expense',
                'Overheads',
            ],
        ],
        AccountTypeGroups::REVENUE   => [
            'increase_action_is_debit' => false,
            'show_on_pl'               => true,
            'show_on_bs'               => false,
            'types'                    => [
                'Other Income',
                'Revenue',
                'Sales',
            ],
        ],
        AccountTypeGroups::LIABILITY => [
            'increase_action_is_debit' => false,
            'show_on_pl'               => false,
            'show_on_bs'               => true,
            'types'                    => [
                'Current Liability',
                'Non-Current Liability',
            ],
        ],
        AccountTypeGroups::EQUITY    => [],
    ];

    /**
     * Seeds the account type groups.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $groupName => $groupInfo) {
            $group = AccountTypeGroup::create([
                'name' => $groupName,
            ]);

            if (!$groupInfo) {
                continue;
            }

            $isDebit  = $groupInfo['increase_action_is_debit'];
            $showOnPl = $groupInfo['show_on_pl'];
            $showOnBs = $groupInfo['show_on_bs'];
            $types    = $groupInfo['types'];

            foreach ($types as $typeName) {
                AccountType::create([
                    'name'                     => $typeName,
                    'account_type_group_id'    => $group->id,
                    'increase_action_is_debit' => $isDebit,
                    'show_on_pl'               => $showOnPl,
                    'show_on_bs'               => $showOnBs,
                ]);
            }
        }
    }
}
