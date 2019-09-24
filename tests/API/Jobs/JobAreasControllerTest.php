<?php

namespace Tests\API\Jobs;

use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\Jobs\Models\JobRoom;
use App\Http\Responses\Jobs\JobRoomListResponse;
use App\Http\Responses\Jobs\JobRoomResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobAreasControllerTest
 *
 * @package Tests\API\SiteSurvey
 * @group   site-survey
 * @group   jobs
 * @group   areas
 * @group   api
 */
class JobAreasControllerTest extends JobTestCase
{
    protected $permissions = ['jobs.view', 'jobs.areas.manage'];

    public function testGetJobRoomList()
    {
        $count = $this->faker->numberBetween(1, 5);
        $job   = $this->fakeJobWithStatus();
        factory(JobRoom::class, $count)->create([
            'job_id' => $job->id,
        ]);
        $url = action('Jobs\JobAreasController@index', [
            'job_id' => $job->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count)
            ->assertValidSchema(JobRoomListResponse::class, true);
    }

    public function testGetJobRoom()
    {
        /** @var JobRoom $room */
        $room = factory(JobRoom::class)->create();
        $url  = action('Jobs\JobAreasController@show', [
            'job_id'  => $room->job_id,
            'room_id' => $room->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(JobRoomResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $room);
    }

    public function testAddJobRoom()
    {
        $job = $this->fakeJobWithStatus();
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $request      = [
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ];
        $url          = action('Jobs\JobAreasController@store', [
            'job_id' => $job->id,
        ]);

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(JobRoomResponse::class, true);
        $data     = $response->getData();
        $reloaded = JobRoom::findOrFail($data['id']);

        self::assertEquals($job->id, $reloaded->job_id);
        self::compareDataWithModel($request, $reloaded);
    }

    public function testUpdateJobRoom()
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobRoom $jobRoom */
        $jobRoom = factory(JobRoom::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $request      = [
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ];
        $url          = action('Jobs\JobAreasController@update', [
            'job_id'  => $job->id,
            'area_id' => $jobRoom->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $data     = $response->getData();
        $reloaded = JobRoom::findOrFail($data['id']);

        self::assertEquals($job->id, $reloaded->job_id);
        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteJobRoom()
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobRoom $jobRoom */
        $jobRoom = factory(JobRoom::class)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobAreasController@destroy', [
            'job_id'  => $job->id,
            'area_id' => $jobRoom->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        JobRoom::query()->where([
            'id'     => $jobRoom->id,
            'job_id' => $job->id,
        ])->firstOrFail();
    }
}
