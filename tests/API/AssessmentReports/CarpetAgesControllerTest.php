<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetAge;
use App\Http\Responses\AssessmentReports\CarpetAgeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class CarpetAgesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class CarpetAgesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllCarpetAges()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(CarpetAge::class, $count)->create();

        $url = action('AssessmentReports\CarpetAgesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneCarpetAge()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetAge $model */
        $model = factory(CarpetAge::class)->create();
        $url   = action('AssessmentReports\CarpetAgesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(CarpetAgeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateCarpetAge()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetAgesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(CarpetAgeResponse::class, true);
        $data     = $response->getData();
        $reloaded = CarpetAge::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateCarpetAge()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetAge $model */
        $model   = factory(CarpetAge::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\CarpetAgesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = CarpetAge::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteCarpetAge()
    {
        /** @var \App\Components\AssessmentReports\Models\CarpetAge $model */
        $model = factory(CarpetAge::class)->create([]);
        $url   = action('AssessmentReports\CarpetAgesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        CarpetAge::findOrFail($model->id);
    }
}
