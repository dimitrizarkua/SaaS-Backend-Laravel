<?php

namespace Tests\Unit\Finance;

use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use Faker\Factory as Faker;

/**
 * Class GLAccountTestFactory
 *
 * @package Tests\Unit\Finance
 */
class GLAccountTestFactory
{
    /**
     * @param int                                                  $accountingOrganizationId
     * @param bool                                                 $increaseIsDebit
     * @param float                                                $balance
     * @param bool                                                 $isBankAccount
     * @param string                                               $code
     * @param \App\Components\Finance\Models\AccountTypeGroup|null $accountTypeGroup
     * @param string                                               $accountTypeName
     *
     * @return \App\Components\Finance\Models\GLAccount
     *
     * @throws \Exception
     */
    public static function createGLAccountWithBalance(
        int $accountingOrganizationId,
        bool $increaseIsDebit,
        float $balance,
        $isBankAccount = true,
        string $code = '',
        AccountTypeGroup $accountTypeGroup = null,
        string $accountTypeName = ''
    ): GLAccount {
        $faker = Faker::create();
        if (null !== $accountTypeGroup) {
            /** @var AccountType $accountType */
            $accountType = factory(AccountType::class)
                ->create([
                    'account_type_group_id'    => $accountTypeGroup->id,
                    'increase_action_is_debit' => $increaseIsDebit,
                    'name'                     => empty($accountTypeName) ? $faker->word : $accountTypeName,
                ]);
        } else {
            $accountType = factory(AccountType::class)
                ->create([
                    'increase_action_is_debit' => $increaseIsDebit,
                    'name'                     => empty($accountTypeName) ? $faker->word : $accountTypeName,
                ]);
        }

        $glAccount = self::getGLAccount(
            $accountType->id,
            $accountingOrganizationId,
            $isBankAccount,
            $code,
            true
        );
        // set balance
        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $accountingOrganizationId,
        ]);

        factory(TransactionRecord::class)->create([
            'gl_account_id'  => $glAccount->id,
            'is_debit'       => $increaseIsDebit,
            'amount'         => $balance,
            'transaction_id' => $transaction->id,
        ]);

        return $glAccount;
    }

    /**
     * @param int    $accountTypeId
     * @param int    $accountingOrganizationId
     * @param bool   $isBankAccount
     * @param string $code
     * @param bool   $enablePaymentsToAccount
     *
     * @return \App\Components\Finance\Models\GLAccount
     *
     * @throws \Exception
     */
    public static function getGLAccount(
        int $accountTypeId,
        int $accountingOrganizationId,
        bool $isBankAccount,
        string $code,
        bool $enablePaymentsToAccount
    ): GLAccount {
        $faker = Faker::create();

        return factory(GLAccount::class)
            ->create([
                'account_type_id'            => $accountTypeId,
                'accounting_organization_id' => $accountingOrganizationId,
                'name'                       => $isBankAccount ? GLAccount::TRADING_BANK_ACCOUNT_NAME : $faker->word,
                'bank_account_name'          => $isBankAccount ? GLAccount::TRADING_BANK_ACCOUNT_NAME : null,
                'is_active'                  => true,
                'code'                       => $code,
                'enable_payments_to_account' => $enablePaymentsToAccount,
            ]);
    }
}
