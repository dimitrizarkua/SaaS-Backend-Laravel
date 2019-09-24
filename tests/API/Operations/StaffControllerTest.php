<?php

namespace Tests\API\Operations;

use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRun;
use App\Http\Responses\Operations\StaffListResponse;
use App\Http\Responses\Operations\StaffResponse;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class StaffControllerTest
 *
 * @package Tests\API\Operations
 * @group   api
 */
class StaffControllerTest extends ApiTestCase
{
    protected $permissions = [
        'operations.staff.view',
    ];

    public function testIndexMethod()
    {
        $location = factory(Location::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        $date  = $this->faker->date();
        factory(User::class, $count)
            ->create()
            ->each(function (User $user) use ($location) {
                $location->users()->attach($user->id);
            });
        $url = action('Operations\StaffController@index', [
            'location_id' => $location->id,
            'date'        => $date,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(StaffListResponse::class, true)
            ->assertJsonCount($count, 'data');
    }

    public function testShowMethod()
    {
        $location = factory(Location::class)->create();
        $user     = factory(User::class)->create();
        $location->users()->attach($user->id);

        /** @var JobTask $jobTask */
        $jobTask = factory(JobTask::class)
            ->create([
                'job_run_id' => factory(JobRun::class)->create([
                    'date' => Carbon::now()->startOfWeek()->toDateString(),
                ])->id,
                'starts_at'  => Carbon::now()->startOfWeek()->subMinutes($this->faker->numberBetween(0, 120)),
                'ends_at'    => Carbon::now()->startOfWeek()->addMinutes($this->faker->numberBetween(0, 120)),
            ]);
        $jobTask->assignedUsers()->attach($user->id);

        /** @var JobTask $jobTaskOtherDay */
        $jobTaskOtherDay = factory(JobTask::class)
            ->create([
                'job_run_id' => factory(JobRun::class)->create([
                    'date' => Carbon::now()->endOfWeek()->toDateString(),
                ])->id,
                'starts_at'  => Carbon::now()->endOfWeek()->subMinutes($this->faker->numberBetween(0, 120)),
                'ends_at'    => Carbon::now()->endOfWeek()->addMinutes($this->faker->numberBetween(0, 120)),
            ]);
        $jobTaskOtherDay->assignedUsers()->attach($user->id);

        $date = Carbon::now()->startOfWeek()->toDateString();

        $url = action('Operations\StaffController@show', [
            'id'   => $user->id,
            'date' => $date,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(StaffResponse::class, true)
            ->getData();

        $dateHours = round($jobTask->starts_at->diffInMinutes($jobTask->ends_at) / 60, 2);
        $weekHours = round(
            $jobTask->starts_at->diffInMinutes($jobTask->ends_at) / 60
            + $jobTaskOtherDay->starts_at->diffInMinutes($jobTaskOtherDay->ends_at) / 60,
            2
        );
        self::assertEquals($weekHours, round($response['week_hours'], 2));
        self::assertEquals($dateHours, round($response['date_hours'], 2));
    }
}
