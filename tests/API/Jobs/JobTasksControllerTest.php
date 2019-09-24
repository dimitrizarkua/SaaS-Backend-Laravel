<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Operations\Models\Vehicle;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Class JobTasksControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobTasksControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.tasks.view', 'jobs.tasks.manage',
    ];

    public function testListJobTasks()
    {
        $job = $this->fakeJobWithStatus();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobTask::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTasksController@listJobTasks', ['job_id' => $job->id]);
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testListJobTasks404()
    {
        $url = action('Jobs\JobTasksController@listJobTasks', [
            'job_id' => $this->faker->randomNumber(),
        ]);
        $this->getJson($url)->assertStatus(404);
    }

    public function testViewJobTasksSuccess()
    {
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@viewJobTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $response = $this->getJson($url);

        $data = $response
            ->assertStatus(200)
            ->assertSeeData()
            ->getData();

        self::assertEquals($jobTask->id, $data['id']);
        self::assertEquals($jobTask->job_task_type_id, $data['job_task_type_id']);
        self::assertEquals($jobTask->job_run_id, $data['job_run_id']);
        self::assertEquals($jobTask->name, $data['name']);
        self::assertEquals($jobTask->internal_note, $data['internal_note']);
        self::assertEquals($jobTask->scheduling_note, $data['scheduling_note']);
        self::assertEquals($jobTask->kpi_missed_reason, $data['kpi_missed_reason']);
        self::assertTrue(Carbon::make($jobTask->due_at)->eq(Carbon::make($data['due_at'])));
        self::assertTrue(Carbon::make($jobTask->starts_at)->eq(Carbon::make($data['starts_at'])));
        self::assertTrue(Carbon::make($jobTask->ends_at)->eq(Carbon::make($data['ends_at'])));
        self::assertArrayHasKey('assigned_users', $data);
        self::assertArrayHasKey('assigned_teams', $data);
        self::assertArrayHasKey('assigned_vehicles', $data);
        self::assertArrayHasKey('latest_status', $data);
        self::assertArrayHasKey('type', $data);
    }

    public function testViewJobTasks404()
    {
        $url = action('Jobs\JobTasksController@viewJobTask', [
            'job_id'  => $this->faker->randomNumber(),
            'task_id' => $this->faker->randomNumber(),
        ]);
        $this->getJson($url)->assertStatus(404);
    }

    public function testAddJobTasksSuccess()
    {
        $job = $this->fakeJobWithStatus();

        $url = action('Jobs\JobTasksController@addJobTask', ['job_id' => $job->id]);

        $request = [
            'job_task_type_id'  => factory(JobTaskType::class)->create()->id,
            'name'              => $this->faker->word,
            'internal_note'     => $this->faker->sentence,
            'scheduling_note'   => $this->faker->sentence,
            'kpi_missed_reason' => $this->faker->sentence,
            'due_at'            => $this->faker->date('Y-m-d\TH:i:s\Z'),
        ];

        $response = $this->postJson($url, $request);

        $data = $response
            ->assertStatus(201)
            ->assertSeeData()
            ->getData();

        $reloaded = JobTask::findOrFail($data['id']);

        foreach ($request as $field => $value) {
            $attributeValue = $reloaded->getAttribute($field);
            if ($attributeValue instanceof Carbon) {
                self::assertTrue($attributeValue->eq(new Carbon($value)));
            } else {
                self::assertEquals($attributeValue, $value);
            }
        }
    }

    public function testAddJobTasks404()
    {
        $url     = action('Jobs\JobTasksController@addJobTask', [
            'job_id' => $this->faker->randomNumber(),
        ]);
        $request = [
            'job_task_type_id'  => factory(JobTaskType::class)->create()->id,
            'name'              => $this->faker->word,
            'internal_note'     => $this->faker->sentence,
            'scheduling_note'   => $this->faker->sentence,
            'kpi_missed_reason' => $this->faker->sentence,
            'due_at'            => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'starts_at'         => $this->faker->dateTimeBetween('now', '+5 days')
                ->format('Y-m-d\TH:i:s\Z'),
            'ends_at'           => $this->faker->dateTimeBetween('+5 days', '+30 days')
                ->format('Y-m-d\TH:i:s\Z'),
        ];
        $this->postJson($url, $request)->assertStatus(404);
    }

    public function testUpdateJobTasksSuccess()
    {
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@updateJobTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $request = [
            'job_task_type_id'  => factory(JobTaskType::class)->create()->id,
            'name'              => $this->faker->word,
            'internal_note'     => $this->faker->sentence,
            'scheduling_note'   => $this->faker->sentence,
            'kpi_missed_reason' => $this->faker->sentence,
            'due_at'            => $this->faker->date('Y-m-d\TH:i:s\Z'),
        ];

        $response = $this->patchJson($url, $request);

        $data = $response
            ->assertStatus(200)
            ->assertSeeData()
            ->getData();

        $reloaded = JobTask::findOrFail($data['id']);

        foreach ($request as $field => $value) {
            $attributeValue = $reloaded->getAttribute($field);
            if ($attributeValue instanceof Carbon) {
                self::assertTrue($attributeValue->eq(new Carbon($value)));
            } else {
                self::assertEquals($attributeValue, $value);
            }
        }
    }

    public function testDeleteJobTasksSuccess()
    {
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@deleteJobTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::assertNull(JobTask::find($jobTask->id));
    }

    public function testChangeJobTaskStatusSuccess()
    {
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@changeStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = $this->faker->randomElement(JobTaskStatuses::values());

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(200);
        $reloaded = JobTask::findOrFail($jobTask->id);

        self::assertEquals($reloaded->latestStatus->status, $newStatus);
    }

    public function testChangeJobTaskStatus404()
    {
        $url = action('Jobs\JobTasksController@changeStatus', [
            'job_id'  => $this->faker->randomNumber(),
            'task_id' => $this->faker->randomNumber(),
        ]);

        $newStatus = $this->faker->randomElement(JobTaskStatuses::values());
        $this->patchJson($url, ['status' => $newStatus])->assertStatus(404);
    }

    public function testChangeJobTaskStatusValidationFail()
    {
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@changeStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = $this->faker->word;

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(422);
    }

    public function testChangeJobTaskScheduledStatusSuccess()
    {
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => true,
        ]);
        $jobTask     = factory(JobTask::class)->create([
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $url = action('Jobs\JobTasksController@changeScheduledStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = $this->faker->randomElement(JobTaskStatuses::values());

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(200);
        $reloaded = JobTask::findOrFail($jobTask->id);

        self::assertEquals($reloaded->latestScheduledStatus->status, $newStatus);
    }

    public function testCompleteBothTaskPortionsSuccess()
    {
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => true,
        ]);
        $jobTask     = factory(JobTask::class)->create([
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $url = action('Jobs\JobTasksController@changeScheduledStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = JobTaskStatuses::COMPLETED;

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(200);
        $reloaded = JobTask::findOrFail($jobTask->id);

        self::assertEquals($reloaded->latestScheduledStatus->status, $newStatus);
        self::assertEquals($reloaded->latestStatus->status, $newStatus);
    }

    public function testChangeJobTaskScheduledStatus404()
    {
        $url = action('Jobs\JobTasksController@changeScheduledStatus', [
            'job_id'  => $this->faker->randomNumber(),
            'task_id' => $this->faker->randomNumber(),
        ]);

        $newStatus = $this->faker->randomElement(JobTaskStatuses::values());
        $this->patchJson($url, ['status' => $newStatus])->assertStatus(404);
    }

    public function testChangeJobTaskScheduledStatusValidationFail()
    {
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => true,
        ]);
        $jobTask     = factory(JobTask::class)->create([
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $url = action('Jobs\JobTasksController@changeScheduledStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = $this->faker->word;

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(422);
    }

    public function testChangeJobTaskScheduledStatusForNonScheduledTaskFail()
    {
        $jobTaskType = factory(JobTaskType::class)->create([
            'can_be_scheduled' => false,
        ]);
        $jobTask     = factory(JobTask::class)->create([
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $url = action('Jobs\JobTasksController@changeScheduledStatus', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $newStatus = $this->faker->randomElement(JobTaskStatuses::values());

        $this->patchJson($url, ['status' => $newStatus])->assertStatus(405);
    }

    public function testAssignUserSuccess()
    {
        $user    = factory(User::class)->create();
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@assignUser', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'user_id' => $user->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded        = JobTask::findOrFail($jobTask->id);
        $assignedUserIds = $reloaded->assignedUsers->pluck('id');

        self::assertCount(1, $assignedUserIds);
        self::assertContains($user->id, $assignedUserIds);
    }

    public function testAssignUserTwiceFail()
    {
        $user    = factory(User::class)->create();
        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedUsers()->attach($user->id);

        $url = action('Jobs\JobTasksController@assignUser', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'user_id' => $user->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignUserSuccess()
    {
        $user = factory(User::class)->create();

        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedUsers()->attach($user->id);

        self::assertCount(1, $jobTask->assignedUsers);

        $url = action('Jobs\JobTasksController@unassignUser', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertCount(0, $reloaded->assignedUsers);
    }

    public function testAssignVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@assignVehicle', [
            'job_id'     => $jobTask->job_id,
            'task_id'    => $jobTask->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded           = JobTask::findOrFail($jobTask->id);
        $assignedVehicleIds = $reloaded->assignedVehicles->pluck('id');

        self::assertCount(1, $assignedVehicleIds);
        self::assertContains($vehicle->id, $assignedVehicleIds);
    }

    public function testAssignVehicleTwiceFail()
    {
        $vehicle = factory(Vehicle::class)->create();
        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedVehicles()->attach($vehicle->id);

        $url = action('Jobs\JobTasksController@assignVehicle', [
            'job_id'     => $jobTask->job_id,
            'task_id'    => $jobTask->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();

        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedVehicles()->attach($vehicle->id);

        self::assertCount(1, $jobTask->assignedVehicles);

        $url = action('Jobs\JobTasksController@unassignVehicle', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'user_id' => $vehicle->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertCount(0, $reloaded->assignedVehicles);
    }

    public function testAssignTeamSuccess()
    {
        $team    = factory(Team::class)->create();
        $jobTask = factory(JobTask::class)->create();

        $url = action('Jobs\JobTasksController@assignTeam', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'team_id' => $team->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded        = JobTask::findOrFail($jobTask->id);
        $assignedTeamIds = $reloaded->assignedTeams->pluck('id');

        self::assertCount(1, $assignedTeamIds);
        self::assertContains($team->id, $assignedTeamIds);
    }

    public function testAssignTeamTwiceFail()
    {
        $team    = factory(Team::class)->create();
        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedTeams()->attach($team->id);

        $url = action('Jobs\JobTasksController@assignTeam', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'team_id' => $team->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignTeamSuccess()
    {
        $team = factory(Team::class)->create();

        $jobTask = factory(JobTask::class)->create();
        $jobTask->assignedTeams()->attach($team->id);

        self::assertCount(1, $jobTask->assignedTeams);

        $url = action('Jobs\JobTasksController@unassignTeam', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
            'user_id' => $team->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertCount(0, $reloaded->assignedTeams);
    }

    public function testSnoozeTaskSuccess()
    {
        $jobTask = factory(JobTask::class)->create();

        $request = [
            'snoozed_until' => Carbon::tomorrow()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $url = action('Jobs\JobTasksController@snoozeTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $this->postJson($url, $request)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::compareDataWithModel($request, $reloaded);
    }

    public function testSnoozeUntilPastFail()
    {
        $jobTask = factory(JobTask::class)->create();

        $request = [
            'snoozed_until' => Carbon::yesterday()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ];

        $url = action('Jobs\JobTasksController@snoozeTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $this->postJson($url, $request)->assertStatus(422);
    }

    public function testUnsnoozeTaskSuccess()
    {
        $jobTask = factory(JobTask::class)->create([
            'snoozed_until' => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        ]);

        $url = action('Jobs\JobTasksController@unsnoozeTask', [
            'job_id'  => $jobTask->job_id,
            'task_id' => $jobTask->id,
        ]);

        $this->delete($url)->assertStatus(200);

        $reloaded = JobTask::findOrFail($jobTask->id);
        self::assertNull($reloaded->snoozed_until);
    }
}
