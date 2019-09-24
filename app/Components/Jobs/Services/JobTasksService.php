<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskScheduledPortionStatus;
use App\Components\Jobs\Models\JobTaskStatus;
use App\Components\Jobs\Models\VO\JobTaskData;
use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Models\HasLatestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class JobTasksService
 *
 * @package App\Components\Jobs\Services
 */
class JobTasksService extends JobsEntityService implements JobTasksServiceInterface
{
    use HasLatestStatus;

    /**
     * {@inheritdoc}
     */
    public function getTask(int $taskId): JobTask
    {
        return JobTask::findOrFail($taskId);
    }

    /**
     * {@inheritdoc}
     */
    public function listJobTasks(int $jobId): Collection
    {
        $job = $this->jobsService()->getJob($jobId);

        return $job->tasks()
            ->with('assignedUsers')
            ->with('assignedTeams')
            ->with('latestStatus')
            ->with('latestScheduledStatus')
            ->with('type')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function listUnscheduledLocationTasks(int $locationId): Collection
    {
        /* @var \App\Components\Locations\Models\Location $location */
        $location = app()->make(LocationsServiceInterface::class)->getLocation($locationId);

        return JobTask::query()
            ->whereNull('job_run_id')
            ->whereNotIn('job_id', $this->getEntityIdsWhereLatestStatusIs('jobs', JobStatuses::$closedStatuses))
            ->whereNotIn('id', $this->getEntityIdsWhereLatestStatusIs('job_tasks', [JobTaskStatuses::COMPLETED]))
            ->whereIn('job_id', $location->assignedJobs()->pluck('id'))
            ->with('job.siteAddress')
            ->with('job.assignedContacts')
            ->with('job.statuses')
            ->with('assignedUsers')
            ->with('assignedTeams')
            ->with('latestStatus')
            ->with('latestScheduledStatus')
            ->with('type')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserTasks(int $userId): Collection
    {
        return JobTask::query()
            ->with('job.siteAddress')
            ->with('job.assignedContacts')
            ->whereHas('assignedUsers', function (Builder $query) use ($userId) {
                $query->where('crew_user_id', '=', $userId);
            })
            ->get();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createTask(JobTaskData $data, int $jobId, int $userId = null): JobTask
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        return DB::transaction(function () use ($data, $job, $userId) {
            $jobTask = new JobTask($data->toArray());

            $jobTask->job_id = $job->id;
            $jobTask->saveOrFail();
            $jobTask->changeStatus(JobTaskStatuses::ACTIVE, $userId);
            $jobTask->refresh();

            return $jobTask;
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function deleteTask(int $taskId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $task->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatus(int $taskId, string $status): JobTaskStatus
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        return $task->changeStatus($status);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function snoozeTask(int $taskId, string $date): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $task->update(['snoozed_until' => $date]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unsnoozeTask(int $taskId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        if (null !== $task->snoozed_until) {
            $task->update(['snoozed_until' => null]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     * @throws \Throwable
     */
    public function changeScheduledStatus(int $taskId, string $status): JobTaskScheduledPortionStatus
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }
        if (!$task->type->can_be_scheduled) {
            throw new NotAllowedException('Tasks of this type cannot be scheduled.');
        }

        return DB::transaction(function () use ($task, $status) {
            if (JobTaskStatuses::COMPLETED === $status) {
                $task->changeStatus($status);
            }

            return $task->changeScheduledStatus($status);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function assignUser(int $taskId, int $userId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $task->assignedUsers()->attach($userId);
        } catch (\Exception $e) {
            throw new NotAllowedException('User is already assigned to this task.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unassignUser(int $taskId, int $userId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $task->assignedUsers()->detach($userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function assignVehicle(int $taskId, int $vehicleId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $task->assignedVehicles()->attach($vehicleId);
        } catch (\Exception $e) {
            throw new NotAllowedException('Vehicle is already assigned to this task.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unassignVehicle(int $taskId, int $vehicleId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $task->assignedVehicles()->detach($vehicleId);
    }

    /**
     * {@inheritdoc}
     */
    public function assignTeam(int $taskId, int $teamId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $task->assignedTeams()->attach($teamId);
        } catch (\Exception $e) {
            throw new NotAllowedException('Team is already assigned to this task.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unassignTeam(int $taskId, int $teamId): void
    {
        $task = $this->getTask($taskId);
        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $task->assignedTeams()->detach($teamId);
    }
}
