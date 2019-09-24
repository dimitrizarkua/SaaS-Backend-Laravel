<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Http\Responses\AssessmentReports\NonRestorableReasonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class NonRestorableReasonsControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class NonRestorableReasonsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllNonRestorableReasons()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(NonRestorableReason::class, $count)->create();

        $url = action('AssessmentReports\NonRestorableReasonsController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneNonRestorableReason()
    {
        /** @var \App\Components\AssessmentReports\Models\NonRestorableReason $model */
        $model = factory(NonRestorableReason::class)->create();
        $url   = action('AssessmentReports\NonRestorableReasonsController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(NonRestorableReasonResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateNonRestorableReason()
    {
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\NonRestorableReasonsController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(NonRestorableReasonResponse::class, true);
        $data     = $response->getData();
        $reloaded = NonRestorableReason::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateNonRestorableReason()
    {
        /** @var \App\Components\AssessmentReports\Models\NonRestorableReason $model */
        $model   = factory(NonRestorableReason::class)->create();
        $request = [
            'name' => $this->faker->word,
        ];
        $url     = action('AssessmentReports\NonRestorableReasonsController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = NonRestorableReason::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteNonRestorableReason()
    {
        /** @var \App\Components\AssessmentReports\Models\NonRestorableReason $model */
        $model = factory(NonRestorableReason::class)->create([]);
        $url   = action('AssessmentReports\NonRestorableReasonsController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        NonRestorableReason::findOrFail($model->id);
    }
}
