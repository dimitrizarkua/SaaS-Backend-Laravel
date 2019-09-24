<?php

namespace Tests\API\Operations;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskStatus;
use App\Components\Locations\Models\Location;
use App\Http\Responses\Operations\TaskListResponse;
use Tests\API\ApiTestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class TasksControllerTest
 *
 * @package Tests\API\Operations
 * @group   jobs
 * @group   api
 */
class TasksControllerTest extends ApiTestCase
{
    use JobFaker;

    protected $permissions = [
        'operations.tasks.view',
    ];

    public function testListLocationTasks()
    {
        $location = factory(Location::class)->create();

        $jobsCount = $this->faker->numberBetween(1, 5);
        $jobs      = factory(Job::class, $jobsCount)->create([
            'assigned_location_id' => $location->id,
        ]);

        $tasksCount = $this->faker->numberBetween(1, 5);
        foreach ($jobs as $job) {
            factory(JobTask::class, $tasksCount)->create([
                'job_id' => $job->id,
            ]);
        }

        $url = action('Operations\TasksController@listLocationTasks', [
            'location_id' => $location->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(TaskListResponse::class, true)
            ->assertJsonCount($jobsCount * $tasksCount, 'data');
    }

    public function testListLocationTasksWithoutClosedOrCanceledJobs()
    {
        $location = factory(Location::class)->create();

        $status = $this->faker->randomElement([JobStatuses::CLOSED, JobStatuses::CANCELLED]);

        $jobsShouldBeHidden = $this->fakeJobsWithStatus($status, ['assigned_location_id' => $location->id]);

        $tasksCount = $this->faker->numberBetween(1, 5);
        foreach ($jobsShouldBeHidden as $job) {
            factory(JobTask::class, $tasksCount)->create([
                'job_id' => $job->id,
            ]);
        }

        $url = action('Operations\TasksController@listLocationTasks', [
            'location_id' => $location->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function testListLocationTasksWithoutCompletedTasks()
    {
        $location = factory(Location::class)->create();

        $status = $this->faker->randomElement(JobStatuses::$activeStatuses);

        $jobsShouldBeHidden = $this->fakeJobsWithStatus($status, ['assigned_location_id' => $location->id]);

        foreach ($jobsShouldBeHidden as $job) {
            $jobTask = factory(JobTask::class)->create([
                'job_id' => $job->id,
            ]);

            factory(JobTaskStatus::class)->create([
                'job_task_id' => $jobTask->id,
                'status'      => JobTaskStatuses::COMPLETED,
            ]);
        }

        $url = action('Operations\TasksController@listLocationTasks', [
            'location_id' => $location->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function testGetMineTasks()
    {
        $tasksCount = $this->faker->numberBetween(1, 5);
        $tasks      = factory(JobTask::class, $tasksCount)->create();
        foreach ($tasks as $task) {
            /* @var \App\Components\Jobs\Models\JobTask $task */
            $task->assignedUsers()->attach($this->user->id);
        }

        $url = action('Operations\TasksController@getMineTasks');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($tasksCount, 'data');
    }
}
