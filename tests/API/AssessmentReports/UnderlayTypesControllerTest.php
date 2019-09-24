<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\UnderlayType;
use App\Http\Responses\AssessmentReports\UnderlayTypeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class UnderlayTypesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class UnderlayTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllUnderlayTypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(UnderlayType::class, $count)->create();

        $url = action('AssessmentReports\UnderlayTypesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneUnderlayType()
    {
        /** @var \App\Components\AssessmentReports\Models\UnderlayType $model */
        $model = factory(UnderlayType::class)->create();
        $url   = action('AssessmentReports\UnderlayTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(UnderlayTypeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateUnderlayType()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\UnderlayTypesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(UnderlayTypeResponse::class, true);
        $data     = $response->getData();
        $reloaded = UnderlayType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateUnderlayType()
    {
        /** @var \App\Components\AssessmentReports\Models\UnderlayType $model */
        $model   = factory(UnderlayType::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\UnderlayTypesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = UnderlayType::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteUnderlayType()
    {
        /** @var \App\Components\AssessmentReports\Models\UnderlayType $model */
        $model = factory(UnderlayType::class)->create([]);
        $url   = action('AssessmentReports\UnderlayTypesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        UnderlayType::findOrFail($model->id);
    }
}
