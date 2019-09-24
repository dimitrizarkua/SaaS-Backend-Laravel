<?php

namespace Tests\API\UsageAndActuals;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class InsurerContractsControllerTest
 *
 * @package Tests\API\UsageAndActuals
 *
 * @group   insurer-contract
 * @group   usage-and-actuals
 */
class InsurerContractsControllerTest extends ApiTestCase
{
    protected $permissions = ['usage_and_actuals.insurer_contracts.view', 'usage_and_actuals.insurer_contracts.manage'];

    /**
     * @var \App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $models        = [
            Contact::class,
            InsurerContract::class,
        ];
        $this->models  = array_merge($models, $this->models);
        $this->service = $this->app->make(InsurerContractsInterface::class);
    }

    public function testCreateMethod()
    {
        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        $contact         = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ]);
        $data            = [
            'contact_id'       => $contact->id,
            'contract_number'  => $this->faker->bankAccountNumber,
            'description'      => $this->faker->text,
            'effect_date'      => Carbon::now()->format('Y-m-d'),
            'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ];

        $url      = action('UsageAndActuals\InsurerContractsController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = InsurerContract::findOrFail($modelId);

        self::assertEquals($model->contact_id, $data['contact_id']);
        self::assertEquals($model->contract_number, $data['contract_number']);
        self::assertEquals($model->description, $data['description']);
        self::assertEquals($model->effect_date->format('Y-m-d'), $data['effect_date']);
        self::assertEquals($model->termination_date->format('Y-m-d'), $data['termination_date']);
    }

    public function testShowMethod()
    {
        /** @var InsurerContract $model */
        $model = factory(InsurerContract::class)->create();
        $url   = action('UsageAndActuals\InsurerContractsController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($model->contact_id, $data['contact_id']);
        self::assertEquals($model->contract_number, $data['contract_number']);
        self::assertEquals($model->description, $data['description']);
        self::assertEquals($model->effect_date->format('Y-m-d'), $data['effect_date']);
        self::assertEquals($model->termination_date->format('Y-m-d'), $data['termination_date']);
    }

    public function testUpdateMethod()
    {
        $oldContract = factory(InsurerContract::class)->create();

        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        $contact         = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ]);
        $data            = [
            'contact_id'       => $contact->id,
            'contract_number'  => $this->faker->bankAccountNumber,
            'description'      => $this->faker->text,
            'effect_date'      => Carbon::now()->format('Y-m-d'),
            'termination_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ];

        $url      = action('UsageAndActuals\InsurerContractsController@update', ['id' => $oldContract->id]);
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $modelId = $response->getData('id');
        $model   = InsurerContract::findOrFail($modelId);

        self::assertEquals($model->contact_id, $data['contact_id']);
        self::assertEquals($model->contract_number, $data['contract_number']);
        self::assertEquals($model->description, $data['description']);
        self::assertEquals($model->effect_date->format('Y-m-d'), $data['effect_date']);
        self::assertEquals($model->termination_date->format('Y-m-d'), $data['termination_date']);
    }

    public function testDestroyMethod()
    {
        /** @var InsurerContract $model */
        $model = factory(InsurerContract::class)->create();
        $url   = action('UsageAndActuals\InsurerContractsController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(InsurerContract::find($model->id));
    }

    public function testGetContractsMethod()
    {
        $insurer = factory(Contact::class)->create();
        $count   = $this->faker->numberBetween(3, 5);
        factory(InsurerContract::class, $count)->create(['contact_id' => $insurer->id]);

        $url      = action('UsageAndActuals\InsurerContractsController@getContracts', [
            'id' => $insurer->id,
        ]);
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJsonDataCount($count);
    }

    public function testGetActiveContractMethod()
    {
        $insurer        = factory(Contact::class)->create();
        $oldContract    = $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(10)->format('Y-m-d'),
                'termination_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            ]
        ));
        $actualContract = $this->service->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurer->id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(3)->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ]
        ));
        $futureContract = $this->service->createContract(new InsurerContractData(
            [
                'contact_id'      => $insurer->id,
                'contract_number' => $this->faker->bankAccountNumber,
                'description'     => $this->faker->text,
                'effect_date'     => Carbon::now()->addDays(10)->format('Y-m-d'),
            ]
        ));

        $url      = action('UsageAndActuals\InsurerContractsController@getActiveContract', [
            'id' => $insurer->id,
        ]);
        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($actualContract->contact_id, $data['contact_id']);
        self::assertEquals($actualContract->contract_number, $data['contract_number']);
        self::assertEquals($actualContract->description, $data['description']);
        self::assertEquals($actualContract->effect_date->format('Y-m-d'), $data['effect_date']);
        self::assertEquals($actualContract->termination_date->format('Y-m-d'), $data['termination_date']);
    }
}
