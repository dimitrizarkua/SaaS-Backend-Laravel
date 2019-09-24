<?php

namespace Tests\Unit\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Collection;

/**
 * Class JobsTestFactory
 *
 * @package Tests
 */
class JobsTestFactory
{
    /**
     * Creates a collection of new jobs with status.
     *
     * @param int   $count      Count of records to create.
     * @param bool  $isActive   Shows whether what category of status should be used for
     *                          job latest status.
     * @param bool  $isAssigned Shows whether should be created job assigned to team or user.
     * @param array $params     Additional params that would be passed to Jobs factory.
     *
     * @return Collection|Job[]
     */
    public static function createJobs(
        int $count = 1,
        bool $isActive = true,
        bool $isAssigned = true,
        array $params = []
    ): Collection {
        $faker      = Factory::create();
        $collection = Collection::make();

        for ($i = 0; $i < $count; $i++) {
            $job = factory(Job::class)->create($params);

            $jobStatus = $isActive
                ? $faker->randomElement(JobStatuses::$activeStatuses)
                : $faker->randomElement(JobStatuses::$closedStatuses);

            factory(JobStatus::class)->create([
                'job_id'  => $job->id,
                'status'  => $jobStatus,
                'user_id' => null,
            ]);

            if ($isAssigned) {
                static::assignJobToUser($job);
            }

            $collection->push($job);
        }

        return $collection;
    }

    /**
     * Assign given job to team.
     *
     * @param \App\Components\Jobs\Models\Job        $job  Job which will be assigned to team.
     * @param \App\Components\Teams\Models\Team|null $team Team for which job will be assigned. If null new team will
     *                                                     be created.
     */
    public static function assignJobToTeam(Job $job, Team $team = null): void
    {
        if (null === $team) {
            $team = \factory(Team::class)->create();
        }

        $job->assignedTeams()->attach($team);
    }

    /**
     * Assign job to user.
     *
     * @param \App\Components\Jobs\Models\Job $job         Job which will be assigned to user.
     * @param \App\Models\User|null           $user        User for who job will be assigned. If null new user will
     *                                                     be created.
     */
    public static function assignJobToUser(Job $job, User $user = null): void
    {
        if (null === $user) {
            $user = \factory(User::class)->create();
        }

        $job->assignedUsers()->attach($user);
    }
}
