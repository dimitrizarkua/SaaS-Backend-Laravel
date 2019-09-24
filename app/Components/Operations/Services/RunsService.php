<?php

namespace App\Components\Operations\Services;

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Operations\Exceptions\NotAllowedException;
use App\Components\Operations\Interfaces\RunsServiceInterface;
use App\Components\Operations\Interfaces\RunTemplatesServiceInterface;
use App\Components\Operations\Interfaces\VehiclesServiceInterface;
use App\Components\Operations\Models\JobRun;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class RunsService
 *
 * @package App\Components\Operations\Services
 */
class RunsService implements RunsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRun(int $runId): JobRun
    {
        return JobRun::findOrFail($runId);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocationRuns(int $locationId, Carbon $date): Collection
    {
        /* @var \App\Components\Locations\Models\Location $location */
        $location = app()->make(LocationsServiceInterface::class)->getLocation($locationId);

        // todo check it and remove seems that assignedTasks.job.siteAddress will be enough
        return $location->runs()
            ->where('date', '=', $date)
            ->with('assignedUsers')
            ->with('assignedVehicles')
            ->with('assignedTasks.job')
            ->with('assignedTasks.job.siteAddress')
            ->with('assignedTasks.assignedUsers')
            ->with('assignedTasks.latestStatus')
            ->with('assignedTasks.type')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function createRun(int $locationId, Carbon $date, string $name = null): JobRun
    {
        return JobRun::create([
            'location_id' => $locationId,
            'date'        => $date,
            'name'        => $name,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function deleteRun(int $runId): void
    {
        $run = $this->getRun($runId);

        try {
            $run->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }

    /**
     * @param \App\Components\Jobs\Models\JobTask $task
     * @param string                              $type
     */
    private function checkTaskBeforeAssign(JobTask $task, $type = 'User')
    {
        $type = ucfirst($type);
        if ($task->job->isClosed()) {
            throw new NotAllowedException(
                sprintf(
                    '%s can not be assigned because run contains task [TASK:%s] assigned to the closed job [JOB_ID:%d]',
                    $type,
                    $task->name,
                    $task->job->id
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     * @throws \Throwable
     */
    public function assignUser(int $runId, int $userId): void
    {
        /** @var JobTasksServiceInterface $tasksService */
        $tasksService = app()->make(JobTasksServiceInterface::class);

        $run = $this->getRun($runId)
            ->load(['assignedUsers', 'assignedTasks.assignedUsers']);

        foreach ($run->assignedTasks as $task) {
            $this->checkTaskBeforeAssign($task);
        }

        DB::transaction(function () use ($run, $userId, $tasksService) {
            if (!$run->assignedUsers->contains($userId)) {
                $run->assignedUsers()->attach($userId);
            }

            foreach ($run->assignedTasks as $task) {
                if (!$task->assignedUsers->contains($userId)) {
                    $task->assignedUsers()->attach($userId);
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function unassignUser(int $runId, int $userId): void
    {
        $run = $this->getRun($runId);
        $run->assignedUsers()->detach($userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     * @throws \Throwable
     */
    public function assignVehicle(int $runId, int $vehicleId): void
    {
        /** @var JobTasksServiceInterface $tasksService */
        $tasksService = app()->make(JobTasksServiceInterface::class);
        /** @var \App\Components\Operations\Models\Vehicle $vehicle */
        $vehicle = app()->make(VehiclesServiceInterface::class)->getVehicle($vehicleId);

        $vehicleStatus = $vehicle->latestStatus;
        if ($vehicleStatus && $vehicleStatus->type->makes_vehicle_unavailable) {
            throw new NotAllowedException('Vehicle is not available at the moment.');
        }

        $run = $this->getRun($runId)
            ->load(['assignedTasks', 'assignedVehicles']);

        if ($vehicle->hasRunsOnDate($run->date)) {
            throw new NotAllowedException('Vehicle already assigned to a run on this date.');
        }

        foreach ($run->assignedTasks as $task) {
            $this->checkTaskBeforeAssign($task, 'Vehicle');
        }

        DB::transaction(function () use ($run, $vehicle, $tasksService) {
            if (!$run->assignedVehicles->contains($vehicle)) {
                $run->assignedVehicles()->attach($vehicle);
            }

            foreach ($run->assignedTasks as $task) {
                if (!$task->assignedVehicles->contains($vehicle)) {
                    $task->assignedVehicles()->attach($vehicle);
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function unassignVehicle(int $runId, int $vehicleId): void
    {
        $run = $this->getRun($runId);
        $run->assignedVehicles()->detach($vehicleId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function scheduleTask(int $runId, int $taskId, Carbon $startsAt, Carbon $endsAt): void
    {
        /** @var JobTasksServiceInterface $tasksService */
        $tasksService = app()->make(JobTasksServiceInterface::class);

        $run = $this->getRun($runId);
        /** @var \App\Components\Jobs\Models\JobTask $task */
        $task = $tasksService->getTask($taskId);

        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        if (!$task->type->can_be_scheduled) {
            throw new NotAllowedException('Tasks of this type cannot be scheduled.');
        }
        if (JobTaskStatuses::ACTIVE !== $task->latestStatus->status) {
            throw new NotAllowedException('Only active tasks can be scheduled.');
        }
        if (null !== $task->job_run_id && $run->id != $task->job_run_id) {
            throw new NotAllowedException('Task is already added to another run.');
        }
        if ($run->hasConflictingTasks($taskId, $startsAt, $endsAt)) {
            throw new NotAllowedException('This time is already reserved for another task.');
        }

        DB::transaction(function () use ($task, $run, $startsAt, $endsAt, $tasksService) {
            foreach ($run->assignedUsers as $user) {
                if (!$task->assignedUsers->contains($user)) {
                    $task->assignedUsers()->attach($user);
                }
            }
            foreach ($run->assignedVehicles as $vehicle) {
                if (!$task->assignedVehicles->contains($vehicle)) {
                    $task->assignedVehicles()->attach($vehicle);
                }
            }

            $task->starts_at  = $startsAt;
            $task->ends_at    = $endsAt;
            $task->job_run_id = $run->id;
            $task->saveOrFail();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function removeTask(int $runId, int $taskId): void
    {
        /** @var \App\Components\Jobs\Models\JobTask $task */
        $task = app()->make(JobTasksServiceInterface::class)->getTask($taskId);
        if ($task->job_run_id !== $runId) {
            throw new NotAllowedException('Task is not assigned to this run.');
        }

        $task->starts_at  = null;
        $task->ends_at    = null;
        $task->job_run_id = null;
        $task->saveOrFail();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createRunsFromTemplate(int $templateId, Carbon $date): Collection
    {
        $template = app()->make(RunTemplatesServiceInterface::class)->getTemplate($templateId);

        return DB::transaction(function () use ($template, $date) {
            $runs = [];
            foreach ($template->runs as $templateRun) {
                $run = $this->createRun($template->location_id, $date, $templateRun->name);

                foreach ($templateRun->assignedUsers as $user) {
                    $this->assignUser($run->id, $user->id);
                }
                foreach ($templateRun->assignedVehicles as $vehicle) {
                    $this->assignVehicle($run->id, $vehicle->id);
                }

                $runs[] = $run;
            }

            return collect($runs);
        });
    }
}
