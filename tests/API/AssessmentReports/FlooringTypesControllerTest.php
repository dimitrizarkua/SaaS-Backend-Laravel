<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\FlooringType;
use App\Http\Responses\AssessmentReports\FlooringTypeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class FlooringTypesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class FlooringTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllFlooringTypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(FlooringType::class, $count)->create();

        $url = action('AssessmentReports\FlooringTypesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneFlooringType()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringType $model */
        $model = factory(FlooringType::class)->create();
        $url   = action('AssessmentReports\FlooringTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(FlooringTypeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateFlooringType()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\FlooringTypesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(FlooringTypeResponse::class, true);
        $data     = $response->getData();
        $reloaded = FlooringType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateFlooringType()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringType $model */
        $model   = factory(FlooringType::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\FlooringTypesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = FlooringType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteFlooringType()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringType $model */
        $model = factory(FlooringType::class)->create([]);
        $url   = action('AssessmentReports\FlooringTypesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        FlooringType::findOrFail($model->id);
    }
}
