<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Http\Responses\AssessmentReports\FlooringSubtypeResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class FlooringSubtypesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class FlooringSubtypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'management.jobs.settings',
    ];

    public function testGetAllFlooringSubtypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(FlooringSubtype::class, $count)->create();

        $url = action('AssessmentReports\FlooringSubtypesController@index');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetOneFlooringSubtype()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringSubtype $model */
        $model = factory(FlooringSubtype::class)->create();
        $url   = action('AssessmentReports\FlooringSubtypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(FlooringSubtypeResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    public function testCreateFlooringSubtype()
    {
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $request      = [
            'flooring_type_id' => $flooringType->id,
            'name'             => $this->faker->word,
        ];
        $url          = action('AssessmentReports\FlooringSubtypesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(FlooringSubtypeResponse::class, true);
        $data     = $response->getData();
        $reloaded = FlooringSubtype::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateFlooringSubtype()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringSubtype $model */
        $model = factory(FlooringSubtype::class)->create();
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $request      = [
            'flooring_type_id' => $flooringType->id,
            'name'             => $this->faker->word,
        ];
        $url          = action('AssessmentReports\FlooringSubtypesController@update', [
            'id' => $model->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = FlooringSubtype::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteFlooringSubtype()
    {
        /** @var \App\Components\AssessmentReports\Models\FlooringSubtype $model */
        $model = factory(FlooringSubtype::class)->create([]);
        $url   = action('AssessmentReports\FlooringSubtypesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        FlooringSubtype::findOrFail($model->id);
    }
}
