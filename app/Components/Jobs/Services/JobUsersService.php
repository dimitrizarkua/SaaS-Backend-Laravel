<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Events\JobAssignedToTeam;
use App\Components\Jobs\Events\JobAssignedToUser;
use App\Components\Jobs\Events\JobUnassignedFromTeam;
use App\Components\Jobs\Events\JobUnassignedFromUser;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Jobs\Models\JobUser;
use App\Components\Teams\Models\TeamMember;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class JobUsersService
 *
 * @package App\Components\Jobs\Services
 */
class JobUsersService extends JobsEntityService implements JobUsersServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function follow(int $jobId, int $userId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $job->followers()->attach($userId);
        } catch (Exception $exception) {
            throw new NotAllowedException('This user is already follows specified job.');
        }
    }

    /**
     *{@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unfollow(int $jobId, int $userId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->followers()->detach($userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function assignToUser(int $jobId, int $userId, int $whoAssignedId = null): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            DB::transaction(function () use (&$job, $userId) {
                $job->assignedUsers()->attach($userId);
                $job->updateTouchedAt();
            });
        } catch (Exception $exception) {
            throw new NotAllowedException('This user is already assigned to specified job.');
        }

        event(new JobAssignedToUser($job, $userId, $whoAssignedId));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unassignFromUser(int $jobId, int $userId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->assignedUsers()->detach($userId);
        event(new JobUnassignedFromUser($job, $userId));
    }

    /**
     * {@inheritdoc}
     */
    public function isUserAssigned(int $jobId, int $userId): bool
    {
        $assignment = JobUser::query()
            ->where([
                'user_id' => $userId,
                'job_id'  => $jobId,
            ])->first();

        if (!$assignment) {
            $teamIds = TeamMember::query()
                ->where('user_id', $userId)
                ->pluck('team_id');
            $assignment = JobTeam::query()
                ->whereIn('team_id', $teamIds)
                ->where('job_id', $jobId)
                ->first();
        }

        return (bool)$assignment;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function assignToTeam(int $jobId, int $teamId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            DB::transaction(function () use (&$job, $teamId) {
                $job->assignedTeams()->attach($teamId);
                $job->updateTouchedAt();
            });
            event(new JobAssignedToTeam($job, $teamId));
        } catch (Exception $exception) {
            throw new NotAllowedException('This team is already assigned to specified job.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unassignFromTeam(int $jobId, int $teamId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->assignedTeams()->detach($teamId);
        event(new JobUnassignedFromTeam($job, $teamId));
    }
}
