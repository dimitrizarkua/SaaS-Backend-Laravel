<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Locations\Models\Location;
use Tests\API\ApiTestCase;

/**
 * Class PaymentsControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 */
class PaymentsControllerTest extends ApiTestCase
{
    public $permissions = ['finance.payments.view', 'finance.payments.create'];

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
            Payment::class,
            Location::class,
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

    public function testIndexMethod()
    {
        /** @var AccountingOrganization $anotherAccountingOrganization */
        $anotherAccountingOrganization = factory(AccountingOrganization::class)->create();

        $locations = factory(Location::class, 2)->create();

        $this->accountingOrganization->locations()->attach($locations[0]);
        $anotherAccountingOrganization->locations()->attach($locations[1]);
        $this->user->locations()->attach($locations[0]);

        $numberOfRecords            = $this->faker->numberBetween(3, 9);
        $numberOfNotRelatedPayments = $this->faker->numberBetween(2, 4);

        factory(Payment::class, $numberOfRecords)->create([
            'transaction_id' => factory(Transaction::class)->create([
                'accounting_organization_id' => $this->accountingOrganization->id,
            ]),
        ]);
        factory(Payment::class, $numberOfNotRelatedPayments)->create([
            'transaction_id' => factory(Transaction::class)->create([
                'accounting_organization_id' => $anotherAccountingOrganization->id,
            ]),
        ]);

        $url = action('Finance\PaymentsController@index');
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testShowMethod()
    {
        $payment = factory(Payment::class)->create();
        $url     = action('Finance\PaymentsController@show', [
            'id' => $payment->io,
        ]);

        $this->getJson($url)
            ->assertStatus(200);
    }

    public function testShowMethodShouldReturnNotFountErrorResponse()
    {
        $url = action('Finance\PaymentsController@show', [
            'id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }
}
