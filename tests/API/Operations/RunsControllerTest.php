<?php

namespace Tests\API\Operations;

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRun;
use App\Components\Operations\Models\JobRunTemplate;
use App\Components\Operations\Models\JobRunTemplateRun;
use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VehicleStatusType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class RunsControllerTest
 *
 * @package Tests\API\Operations
 * @group   jobs
 * @group   api
 */
class RunsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'operations.runs.view', 'operations.runs.manage',
    ];

    public function testListLocationRuns()
    {
        $location = factory(Location::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        $date  = $this->faker->date();
        factory(JobRun::class, $count)->create([
            'location_id' => $location->id,
            'date'        => $date,
        ]);

        $url = action('Operations\RunsController@listLocationRuns', [
            'location_id' => $location->id,
            'date'        => $date,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testViewRunSuccess()
    {
        $run = factory(JobRun::class)->create();

        $url = action('Operations\RunsController@show', [
            'run_id' => $run->id,
        ]);

        $data = $this->getJson($url)->assertStatus(200)->getData();
        self::assertEquals($data['id'], $run->id);
        self::assertEquals($data['name'], $run->name);
        self::assertArrayHasKey('assigned_users', $data);
        self::assertArrayHasKey('assigned_vehicles', $data);
        self::assertArrayHasKey('assigned_tasks', $data);
    }

    public function testViewRun404()
    {
        $url = action('Operations\RunsController@show', [
            'run_id' => $this->faker->randomNumber(),
        ]);

        $this->getJson($url)->assertStatus(404);
    }

    public function testCreateRunSuccess()
    {
        $location = factory(Location::class)->create();

        $data = [
            'location_id' => $location->id,
            'date'        => $this->faker->date(),
            'name'        => $this->faker->word,
        ];

        $url = action('Operations\RunsController@store');

        $data = $this->postJson($url, $data)
            ->assertStatus(201)
            ->getData();

        $reloaded = JobRun::findOrFail($data['id']);
        self::assertEquals($data['location_id'], $reloaded->location_id);
        self::assertTrue(Carbon::make($data['date'])->eq(Carbon::make($reloaded->date)));
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testUpdateRunSuccess()
    {
        $run = factory(JobRun::class)->create();

        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunsController@update', [
            'run_id' => $run->id,
        ]);

        $data = $this->patchJson($url, $data)
            ->assertStatus(200)
            ->getData();

        $reloaded = JobRun::findOrFail($data['id']);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testUpdateRun404()
    {
        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunsController@update', [
            'run_id' => $this->faker->randomNumber(),
        ]);

        $this->patchJson($url, $data)->assertStatus(404);
    }

    public function testDeleteRunSuccess()
    {
        $run = factory(JobRun::class)->create();

        $url = action('Operations\RunsController@destroy', [
            'run_id' => $run->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::assertNull(JobRun::find($run->id));
    }

    public function testDeleteRun404()
    {
        $url = action('Operations\RunsController@destroy', [
            'run_id' => $this->faker->randomNumber(),
        ]);

        $this->deleteJson($url)->assertStatus(404);
    }

    public function testAssignUserSuccess()
    {
        $user   = factory(User::class)->create();
        $jobRun = factory(JobRun::class)->create();

        $url = action('Operations\RunsController@assignUser', [
            'run_id'  => $jobRun->id,
            'user_id' => $user->id,
        ]);

        $this->postJson($url)
            ->assertStatus(200);

        $reloaded        = JobRun::findOrFail($jobRun->id);
        $assignedUserIds = $reloaded->assignedUsers->pluck('id');

        self::assertCount(1, $assignedUserIds);
        self::assertContains($user->id, $assignedUserIds);
    }

    public function testAssignUserTwiceAndOnlyOneAssignedUser()
    {
        $user   = factory(User::class)->create();
        $jobRun = factory(JobRun::class)->create();
        $jobRun->assignedUsers()->attach($user);

        $url = action('Operations\RunsController@assignUser', [
            'run_id'  => $jobRun->id,
            'user_id' => $user->id,
        ]);

        $this->postJson($url)
            ->assertStatus(200);

        self::assertCount(1, $jobRun->assignedUsers);
    }

    public function testUnassignUserSuccess()
    {
        $user = factory(User::class)->create();

        $jobRun = factory(JobRun::class)->create();
        $jobRun->assignedUsers()->attach($user->id);

        self::assertCount(1, $jobRun->assignedUsers);

        $url = action('Operations\RunsController@unassignUser', [
            'run_id'  => $jobRun->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobRun::findOrFail($jobRun->id);
        self::assertCount(0, $reloaded->assignedUsers);
    }

    public function testAssignVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();
        $jobRun  = factory(JobRun::class)->create();

        $url = action('Operations\RunsController@assignVehicle', [
            'run_id'     => $jobRun->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded           = JobRun::findOrFail($jobRun->id);
        $assignedVehicleIds = $reloaded->assignedVehicles->pluck('id');

        self::assertCount(1, $assignedVehicleIds);
        self::assertContains($vehicle->id, $assignedVehicleIds);
    }

    public function testAssignVehicleTwiceFail()
    {
        $vehicle = factory(Vehicle::class)->create();
        $jobRun  = factory(JobRun::class)->create();
        $jobRun->assignedVehicles()->attach($vehicle);

        $url = action('Operations\RunsController@assignVehicle', [
            'run_id'     => $jobRun->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testAssignVehicleOnSameDateFail()
    {
        $vehicle = factory(Vehicle::class)->create();
        $jobRun1 = factory(JobRun::class)->create();
        $jobRun2 = factory(JobRun::class)->create([
            'date' => $jobRun1->date,
        ]);
        $jobRun1->assignedVehicles()->attach($vehicle);

        $url = action('Operations\RunsController@assignVehicle', [
            'run_id'     => $jobRun2->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testAssignNotAvailableVehicleFail()
    {
        /** @var Vehicle $vehicle */
        $vehicle = factory(Vehicle::class)->create();
        $jobRun  = factory(JobRun::class)->create();

        $vehicleStatus = factory(VehicleStatusType::class)->create([
            'makes_vehicle_unavailable' => true,
        ]);
        $vehicle->changeStatus($vehicleStatus, $this->user->id);

        $url = action('Operations\RunsController@assignVehicle', [
            'run_id'     => $jobRun->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();

        $jobRun = factory(JobRun::class)->create();
        $jobRun->assignedVehicles()->attach($vehicle->id);

        self::assertCount(1, $jobRun->assignedVehicles);

        $url = action('Operations\RunsController@unassignVehicle', [
            'run_id'  => $jobRun->id,
            'user_id' => $vehicle->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobRun::findOrFail($jobRun->id);
        self::assertCount(0, $reloaded->assignedVehicles);
    }

    public function testScheduleTaskSuccess()
    {
        $jobTask = $this->getTasksAvailableForScheduling();
        $jobRun  = factory(JobRun::class)->create();

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask->id,
        ]);

        $request = [
            'starts_at' => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
            'ends_at'   => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $this->postJson($url, $request)->assertStatus(200);

        $reloaded        = JobRun::findOrFail($jobRun->id);
        $assignedTaskIds = $reloaded->assignedTasks->pluck('id');

        self::assertCount(1, $assignedTaskIds);
        self::assertContains($jobTask->id, $assignedTaskIds);
        self::assertTrue(
            $reloaded->assignedTasks[0]->starts_at->eq(Carbon::make($request['starts_at']))
        );
        self::assertTrue(
            $reloaded->assignedTasks[0]->ends_at->eq(Carbon::make($request['ends_at']))
        );
    }

    public function testScheduleTaskTwiceSuccess()
    {
        $jobRun  = factory(JobRun::class)->create();
        $jobTask = $this->getTasksAvailableForScheduling([
            'job_run_id' => $jobRun->id,
        ]);

        $request = [
            'starts_at' => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
            'ends_at'   => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask->id,
        ]);

        $this->postJson($url, $request)->assertStatus(200);
    }

    public function testScheduleToAnotherRunFail()
    {
        $jobRun  = factory(JobRun::class)->create();
        $jobTask = $this->getTasksAvailableForScheduling([
            'job_run_id' => $jobRun->id,
        ]);

        $jobRun2 = factory(JobRun::class)->create();

        $request = [
            'starts_at' => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
            'ends_at'   => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun2->id,
            'task_id' => $jobTask->id,
        ]);

        $this->postJson($url, $request)->assertStatus(405);
    }

    public function testScheduleConflictingTaskFail()
    {
        $jobRun   = factory(JobRun::class)->create();
        $jobTask1 = $this->getTasksAvailableForScheduling([
            'job_run_id' => $jobRun->id,
        ]);

        $jobTask2 = $this->getTasksAvailableForScheduling();
        $request  = [
            'starts_at' => $jobTask1->starts_at,
            'ends_at'   => $jobTask1->ends_at,
        ];

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask2->id,
        ]);

        $this->postJson($url, $request)->assertStatus(405);
    }

    public function testScheduleNonSchedulableTaskFail()
    {
        $jobRun      = factory(JobRun::class)->create();
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => false,
        ]);
        $jobTask     = $this->getTasksAvailableForScheduling([
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask->id,
        ]);

        $request = [
            'starts_at' => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
            'ends_at'   => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $this->postJson($url, $request)->assertStatus(405);
    }

    public function testRemoveTaskSuccess()
    {
        $jobRun  = factory(JobRun::class)->create();
        $jobTask = factory(JobTask::class)->create([
            'job_run_id' => $jobRun->id,
        ]);
        $jobRun->refresh();

        self::assertCount(1, $jobRun->assignedTasks);

        $url = action('Operations\RunsController@removeTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobRun::findOrFail($jobRun->id);
        self::assertCount(0, $reloaded->assignedTasks);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertNull($reloaded->job_run_id);
        self::assertNull($reloaded->starts_at);
        self::assertNull($reloaded->ends_at);
    }

    public function testAssignmentsWhenSchedulingTask()
    {
        $jobTask = $this->getTasksAvailableForScheduling();

        /** @var JobRun $jobRun */
        $jobRun = factory(JobRun::class)->create();

        $vehiclesCount = $this->faker->numberBetween(1, 5);
        $vehicles      = factory(Vehicle::class, $vehiclesCount)->create();
        foreach ($vehicles as $vehicle) {
            $jobRun->assignedVehicles()->attach($vehicle->id);
        }

        $usersCount = $this->faker->numberBetween(1, 5);
        $users      = factory(User::class, $usersCount)->create();
        foreach ($users as $user) {
            $jobRun->assignedUsers()->attach($user->id);
        }

        $url = action('Operations\RunsController@scheduleTask', [
            'run_id'  => $jobRun->id,
            'task_id' => $jobTask->id,
        ]);

        $request = [
            'starts_at' => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
            'ends_at'   => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $this->postJson($url, $request)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertCount($vehiclesCount, $reloaded->assignedVehicles);
        self::assertCount($usersCount, $reloaded->assignedUsers);
    }

    public function testAssignmentsWhenAddingVehicle()
    {
        /** @var JobRun $jobRun */
        $jobRun = factory(JobRun::class)->create();

        $tasksCount = $this->faker->numberBetween(2, 5);
        $tasks      = $this->getTasksAvailableForScheduling([
            'job_run_id' => $jobRun->id,
        ], $tasksCount);

        $vehicle = factory(Vehicle::class)->create();

        $url = action('Operations\RunsController@assignVehicle', [
            'run_id'     => $jobRun->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        foreach ($tasks as $task) {
            $reloaded = JobTask::findOrFail($task->id);
            self::assertCount(1, $reloaded->assignedVehicles);
            self::assertEquals($vehicle->id, $reloaded->assignedVehicles[0]->id);
        }
    }

    public function testAssignmentsWhenAddingUser()
    {
        /** @var JobRun $jobRun */
        $jobRun = factory(JobRun::class)->create();

        $tasksCount = $this->faker->numberBetween(2, 5);
        $tasks      = $this->getTasksAvailableForScheduling([
            'job_run_id' => $jobRun->id,
        ], $tasksCount);

        $user = factory(User::class)->create();

        $url = action('Operations\RunsController@assignUser', [
            'run_id'  => $jobRun->id,
            'user_id' => $user->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        foreach ($tasks as $task) {
            $reloaded = JobTask::findOrFail($task->id);
            self::assertCount(1, $reloaded->assignedUsers);
            self::assertEquals($user->id, $reloaded->assignedUsers[0]->id);
        }
    }

    public function testCreateFromTemplateSuccess()
    {
        $template = factory(JobRunTemplate::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobRunTemplateRun::class, $count)->create([
            'job_run_template_id' => $template->id,
        ]);

        $date = $this->faker->date();
        $url  = action('Operations\RunsController@createFromTemplate', [
            'template_id' => $template->id,
        ]);

        $data = $this->postJson($url, ['date' => $date])
            ->assertStatus(200)
            ->getData();

        self::assertCount($count, $data);
        foreach ($data as $runData) {
            $run = JobRun::findOrFail($runData['id']);
            self::assertTrue(Carbon::make($date)->eq($run->date));
        }
    }

    /**
     * @param array $attributes
     * @param int   $count
     *
     * @return \App\Components\Jobs\Models\JobTask|\Illuminate\Support\Collection
     */
    private function getTasksAvailableForScheduling(array $attributes = [], int $count = 1)
    {
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => true,
        ]);

        $attributes = $attributes + ['job_task_type_id' => $jobTaskType->id];

        /** @var JobTask $tasks */
        $tasks = factory(JobTask::class, $count)->create($attributes);
        foreach ($tasks as $task) {
            $task->changeStatus(JobTaskStatuses::ACTIVE);
        }

        return $count > 1 ? $tasks : $tasks[0];
    }
}
