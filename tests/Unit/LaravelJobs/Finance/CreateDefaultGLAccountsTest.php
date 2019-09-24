<?php

namespace Tests\Unit\LaravelJobs\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Jobs\Finance\CreateDefaultGLAccounts;
use Tests\TestCase;

/**
 * Class CreateDefaultGLAccountsTest
 *
 * @package Tests\Unit\LaravelJobs\Finance
 */
class CreateDefaultGLAccountsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->seed('AccountTypeGroupsSeeder');
        $this->seed('TaxRatesSeeder');
    }

    /**
     * @throws \Throwable
     */
    public function testCreateDefaultGLAccountsForNewOrganization()
    {
        /** @var AccountingOrganization $organization */
        $organization = factory(AccountingOrganization::class)->create();
        self::assertFalse($organization->glAccounts()->exists());

        (new CreateDefaultGLAccounts($organization->id))->handle();

        $organization->refresh();

        self::assertCount(23, $organization->glAccounts);

        self::assertNotNull($organization->tax_payable_account_id);
        self::assertNotNull($organization->tax_receivable_account_id);
        self::assertNotNull($organization->accounts_payable_account_id);
        self::assertNotNull($organization->accounts_receivable_account_id);
        self::assertNotNull($organization->payment_details_account_id);
    }
}
