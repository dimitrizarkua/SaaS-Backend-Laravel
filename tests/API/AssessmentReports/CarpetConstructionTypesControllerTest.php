<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Http\Responses\AssessmentReports\CarpetConstructionTypeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class CarpetConstructionTypesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class CarpetConstructionTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllCarpetConstructionTypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(CarpetConstructionType::class, $count)->create();

        $url = action('AssessmentReports\CarpetConstructionTypesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneCarpetConstructionType()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetConstructionType $model */
        $model = factory(CarpetConstructionType::class)->create();
        $url   = action('AssessmentReports\CarpetConstructionTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(CarpetConstructionTypeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateCarpetConstructionType()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetConstructionTypesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(CarpetConstructionTypeResponse::class, true);
        $data     = $response->getData();
        $reloaded = CarpetConstructionType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateCarpetConstructionType()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetConstructionType $model */
        $model   = factory(CarpetConstructionType::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetConstructionTypesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = CarpetConstructionType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteCarpetConstructionType()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetConstructionType $model */
        $model = factory(CarpetConstructionType::class)->create([]);
        $url   = action('AssessmentReports\CarpetConstructionTypesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        CarpetConstructionType::findOrFail($model->id);
    }
}
