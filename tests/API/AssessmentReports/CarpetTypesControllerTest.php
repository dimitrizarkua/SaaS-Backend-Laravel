<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetType;
use App\Http\Responses\AssessmentReports\CarpetTypeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class CarpetTypesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class CarpetTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllCarpetTypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(CarpetType::class, $count)->create();

        $url = action('AssessmentReports\CarpetTypesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneCarpetType()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetType $model */
        $model = factory(CarpetType::class)->create();
        $url   = action('AssessmentReports\CarpetTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(CarpetTypeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateCarpetType()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetTypesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(CarpetTypeResponse::class, true);
        $data     = $response->getData();
        $reloaded = CarpetType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateCarpetType()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetType $model */
        $model   = factory(CarpetType::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetTypesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = CarpetType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteCarpetTypes()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetType $model */
        $model = factory(CarpetType::class)->create([]);
        $url   = action('AssessmentReports\CarpetTypesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        CarpetType::findOrFail($model->id);
    }
}
