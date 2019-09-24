<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Http\Responses\Finance\GLAccountListResponse;
use App\Http\Responses\Finance\GLAccountResponse;
use App\Http\Responses\Finance\GLAccountSearchListResponse;
use Tests\API\ApiTestCase;

/**
 * Class GLAccountsControllerTest
 *
 * @package Tests\API\Finance
 * @group   gl-accounts
 * @group   finance
 */
class GLAccountsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.gl_accounts.manage',
        'finance.gl_accounts.view',
        'finance.gl_accounts.reports.view',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            GLAccount::class,
            AccountingOrganization::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganizationLocation::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords        = $this->faker->numberBetween(2, 3);
        $accountingOrganization = factory(AccountingOrganization::class)->create();

        /** @var GLAccount $glAccount */
        $glAccounts = factory(GLAccount::class, $numberOfRecords)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $url = action(
            'Finance\GLAccountsController@index',
            [
                'accounting_organization' => $glAccounts[0]->accounting_organization_id,
            ]
        );

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords)
            ->assertValidSchema(GLAccountListResponse::class, true);
    }

    public function testIndexMethodWithFiltration()
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create();

        $numberOfRecordsDebit = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsDebit)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create(['increase_action_is_debit' => true]),
        ]);

        $numberOfRecordsCredit = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsCredit)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create(['increase_action_is_debit' => false]),
        ]);

        $url = action(
            'Finance\GLAccountsController@index',
            [
                'accounting_organization' => $accountingOrganization->id,
                'is_debit'                => true,
            ]
        );

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecordsDebit)
            ->assertValidSchema(GLAccountListResponse::class, true);
    }

    public function testCreateMethod()
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $data                   = [
            'account_type_id' => factory(AccountType::class)->create()->id,
            'tax_rate_id'     => factory(TaxRate::class)->create()->id,
            'code'            => $this->faker->word,
            'name'            => $this->faker->word,
            'status'          => $this->faker->word,
        ];

        $url      = action(
            'Finance\GLAccountsController@store',
            [
                'accounting_organization' => $accountingOrganization->id,
            ]
        );
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = GLAccount::findOrFail($modelId);
        self::assertEquals($accountingOrganization->id, $model->accounting_organization_id);
        self::assertEquals($data['account_type_id'], $model->account_type_id);
        self::assertEquals($data['tax_rate_id'], $model->tax_rate_id);
        self::assertEquals($data['code'], $model->code);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['status'], $model->status);
    }

    public function testCreateMethodShouldReturnValidationError()
    {
        $data                   = [];
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $url                    = action(
            'Finance\GLAccountsController@store',
            [
                'accounting_organization' => $accountingOrganization->id,
            ]
        );
        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var GLAccount $model */
        $model = factory(GLAccount::class)->create();

        $url = action('Finance\GLAccountsController@show', [
            'gl_account'              => $model->id,
            'accounting_organization' => $model->accounting_organization_id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(GLAccountResponse::class, true);
    }

    public function testShowMethodShouldReturnNotFoundErrorIfOrganizationNotExist()
    {
        $model = factory(GLAccount::class)->create();

        $url = action('Finance\GLAccountsController@show', [
            'accounting_organization' => 0,
            'gl_account'              => $model->id,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testShowMethodShouldReturnNotFoundErrorIfGLAccountNotExist()
    {
        $model = factory(GLAccount::class)->create();

        $url = action('Finance\GLAccountsController@show', [
            'accounting_organization' => $model->accounting_organization_id,
            'gl_account'              => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdateMethod()
    {
        /** @var GLAccount $model */
        $model = factory(GLAccount::class)->create();

        $url = action('Finance\GLAccountsController@update', [
            'accounting_organization' => $model->accounting_organization_id,
            'gl_account'              => $model->id,
        ]);

        $data = [
            'accounting_organization_id' => factory(AccountingOrganization::class)->create()->id,
            'account_type_id'            => factory(AccountType::class)->create()->id,
            'tax_rate_id'                => factory(TaxRate::class)->create()->id,
            'code'                       => $this->faker->word . ' new_code_value',
            'name'                       => $this->faker->word . ' new_name_value',
            'status'                     => $this->faker->word . ' new_status_value',
        ];

        $response = $this->patchJson($url, $data);

        $response->assertStatus(200)
            ->assertValidSchema(GLAccountResponse::class, true);

        $reloaded = GLAccount::findOrFail($model->id);
        self::assertEquals($data['code'], $reloaded->code);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['status'], $reloaded->status);
    }

    public function testUpdateMethodShouldReturnValidationError()
    {
        /** @var GLAccount $model */
        $model = factory(GLAccount::class)->create();
        $url   = action('Finance\GLAccountsController@update', [
            'gl_account'              => $model->id,
            'accounting_organization' => $model->accounting_organization_id,
        ]);

        $data = [
            'account_type_id' => 0,
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(422);
    }

    public function testSearchMethodShouldReturnExceptedResultSet()
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $model = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = [
            'gl_account_id' => $model->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount(1)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodShouldReturnEmptyData()
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => false]);

        $model = factory(GLAccount::class)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = ['gl_account_id' => $model->id];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount(0)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodResultSetFilteringByOrganizationId()
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $cnt = $this->faker->numberBetween(2, 5);

        factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = [
            'accounting_organization_id' => $organization->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($cnt)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodShouldReturnExceptedResultSetWithComplexFilter()
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $cnt = $this->faker->numberBetween(2, 5);

        $glAccounts = factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = [
            'accounting_organization_id' => $organization->id,
            'gl_account_id'              => $glAccounts->first()->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount(1)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodShouldReturnExceptedResultSetWithLocations()
    {
        $organization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $cnt = $this->faker->numberBetween(2, 5);

        $glAccounts = factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation */
        $accountingLocation = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $filter = [
            'locations'     => [$accountingLocation->location_id],
            'gl_account_id' => $glAccounts->first()->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount(1)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodShouldReturnExceptedEmptyResultSetWithLocations()
    {
        $organization1 = factory(AccountingOrganization::class)->create(['is_active' => true]);
        $organization2 = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $cnt = $this->faker->numberBetween(2, 5);

        $glAccounts1 = factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization1->id,
        ]);

        factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization2->id,
        ]);

        factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization1->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation2 */
        $accountingLocation2 = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization2->id,
        ]);

        $filter = [
            'locations'     => [$accountingLocation2->location_id],
            'gl_account_id' => $glAccounts1->first()->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount(0)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchMethodShouldReturnExceptedResultSetWhenTwoLocationsWasSet()
    {
        $organization1 = factory(AccountingOrganization::class)->create(['is_active' => true]);
        $organization2 = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $cnt = $this->faker->numberBetween(2, 5);

        $glAccounts1 = factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization1->id,
        ]);

        factory(GLAccount::class, $cnt)->create([
            'is_active'                  => true,
            'accounting_organization_id' => $organization2->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation1 */
        $accountingLocation1 = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization1->id,
        ]);

        /** @var AccountingOrganizationLocation $accountingLocation2 */
        $accountingLocation2 = factory(AccountingOrganizationLocation::class)->create([
            'accounting_organization_id' => $organization2->id,
        ]);

        $filter = [
            'locations'     => [$accountingLocation1->location_id, $accountingLocation2->location_id],
            'gl_account_id' => $glAccounts1->first()->id,
        ];

        $url = action('Finance\GLAccountsController@search', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount(1)
            ->assertValidSchema(GLAccountSearchListResponse::class, true);
    }

    public function testSearchWithFiltrationByDebitType()
    {
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);
        $accountingOrganization->locations()->attach($location);

        $numberOfRecordsDebit = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsDebit)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'is_active'                  => true,
            'account_type_id'            => factory(AccountType::class)->create(['increase_action_is_debit' => true]),
        ]);

        $numberOfRecordsCredit = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsCredit)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'is_active'                  => true,
            'account_type_id'            => factory(AccountType::class)->create(['increase_action_is_debit' => false]),
        ]);

        $url = action('Finance\GLAccountsController@search', ['is_debit' => true]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecordsDebit);
    }

    public function testSearchWithFiltrationByEnablePaymentsToAccountField()
    {
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);
        $accountingOrganization->locations()->attach($location);

        $numberOfRecordsEnabledPayments = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsEnabledPayments)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'is_active'                  => true,
            'enable_payments_to_account' => true,
        ]);

        $numberOfRecordsDisabledPayments = $this->faker->numberBetween(2, 3);
        factory(GLAccount::class, $numberOfRecordsDisabledPayments)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'is_active'                  => true,
            'enable_payments_to_account' => false,
        ]);

        $url = action('Finance\GLAccountsController@search', ['enable_payments_to_account' => true]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecordsEnabledPayments);
    }
}
