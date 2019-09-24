<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Http\Responses\AssessmentReports\CarpetFaceFibreResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class CarpetFaceFibresControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class CarpetFaceFibresControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllCarpetFaceFibres()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(CarpetFaceFibre::class, $count)->create();

        $url = action('AssessmentReports\CarpetFaceFibresController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneCarpetFaceFibre()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetFaceFibre $model */
        $model = factory(CarpetFaceFibre::class)->create();
        $url   = action('AssessmentReports\CarpetFaceFibresController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(CarpetFaceFibreResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateCarpetFaceFibre()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetFaceFibresController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(CarpetFaceFibreResponse::class, true);
        $data     = $response->getData();
        $reloaded = CarpetFaceFibre::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateCarpetFaceFibre()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetFaceFibre $model */
        $model   = factory(CarpetFaceFibre::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetFaceFibresController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = CarpetFaceFibre::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteCarpetFaceFibre()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetFaceFibre $model */
        $model = factory(CarpetFaceFibre::class)->create([]);
        $url   = action('AssessmentReports\CarpetFaceFibresController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        CarpetFaceFibre::findOrFail($model->id);
    }
}
