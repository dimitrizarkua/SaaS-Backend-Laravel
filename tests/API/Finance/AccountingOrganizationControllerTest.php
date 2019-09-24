<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use Tests\API\ApiTestCase;

/**
 * Class AccountingOrganizationControllerTest
 *
 * @package Tests\API\Finance
 *
 * @group   accounting-organization
 * @group   finance
 */
class AccountingOrganizationControllerTest extends ApiTestCase
{
    protected $permissions = ['finance.accounting_organizations.manage'];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            TaxRate::class,
            AccountType::class,
            GLAccount::class,
            AccountingOrganization::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(2, 4);
        factory(AccountingOrganization::class, $numberOfRecords)->create();

        $url      = action('Finance\AccountingOrganizationsController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testCreateMethod()
    {
        $location        = factory(Location::class)->create();
        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::COMPANY_LOCATION,
        ]);

        $data = [
            'contact_id'        => factory(Contact::class)->create([
                'contact_category_id' => $contactCategory->id,
                'contact_type'        => ContactTypes::COMPANY,
            ])->id,
            'lock_day_of_month' => $this->faker->numberBetween(1, 31),
            'location_id'       => $location->id,
        ];

        $url      = action('Finance\AccountingOrganizationsController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = AccountingOrganization::findOrFail($modelId);

        self::assertEquals($model->contact_id, $data['contact_id']);
        self::assertEquals($model->lock_day_of_month, $data['lock_day_of_month']);
        self::assertTrue($model->is_active);
        self::assertEquals($location->id, $model->locations->first()->id);
        self::assertCount(1, $model->locations);
    }

    public function testCreateMethodShouldReturnValidationError()
    {
        $data = [];
        $url  = action('Finance\AccountingOrganizationsController@store');
        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testUpdateMethod()
    {
        $model = factory(AccountingOrganization::class)->create();
        $url   = action('Finance\AccountingOrganizationsController@update', [
            'id' => $model->id,
        ]);

        $taxPayable     = factory(GLAccount::class)->create([
            'accounting_organization_id' => $model->id,
        ]);
        $paymentDetails = factory(GLAccount::class)->create([
            'accounting_organization_id' => $model->id,
        ]);

        $data = [
            'tax_payable_account_id'     => $taxPayable->id,
            'payment_details_account_id' => $paymentDetails->id,
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = AccountingOrganization::findOrFail($model->id);
        self::assertEquals($data['tax_payable_account_id'], $reloaded->tax_payable_account_id);
        self::assertEquals($data['payment_details_account_id'], $reloaded->payment_details_account_id);
    }

    public function testUpdateMethodShouldReturnValidationError()
    {
        $model = factory(AccountingOrganization::class)->create();
        $url   = action('Finance\AccountingOrganizationsController@update', [
            'id' => $model->id,
        ]);

        $glAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create()->id,
        ]);

        $data = [
            'tax_payable_account_id' => $glAccount->id,
        ];
        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testAttachLocationMethod()
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $location               = factory(Location::class)->create();

        $url = action('Finance\AccountingOrganizationsController@addLocation', [
            'accounting_organization' => $accountingOrganization->id,
            'location'                => $location->id,
        ]);
        $this->postJson($url)
            ->assertStatus(200);

        $reloaded = AccountingOrganization::findOrFail($accountingOrganization->id);
        self::assertCount(1, $reloaded->locations);
    }

    public function testGetLocations()
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create();

        $numberOfRecords = $this->faker->numberBetween(1, 4);
        factory(Location::class, $numberOfRecords)->create()
            ->each(function (Location $location) use ($accountingOrganization) {
                $accountingOrganization->locations()->attach($location);
            });

        $url = action('Finance\AccountingOrganizationsController@getLocations', [
            'accounting_organization' => $accountingOrganization->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }
}
