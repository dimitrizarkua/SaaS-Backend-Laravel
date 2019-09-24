<?php

namespace Tests\Unit\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobStatus;
use Illuminate\Support\Carbon;

/**
 * Trait JobFaker
 *
 * @package Tests\Unit\Jobs
 * @property \Faker\Generator faker
 */
trait JobFaker
{
    /**
     * @param string|null $status
     * @param array       $attributes
     *
     * @return Job
     */
    protected function fakeJobWithStatus(string $status = null, array $attributes = []): Job
    {
        $job = factory(Job::class)->create($attributes);

        if (!$status) {
            $status = $this->faker->randomElement([
                JobStatuses::NEW,
                JobStatuses::IN_PROGRESS,
                JobStatuses::ON_HOLD,
            ]);
        }
        factory(JobStatus::class)->create([
            'job_id'     => $job->id,
            'status'     => $status,
            'created_at' => Carbon::now(),
        ]);

        return $job->fresh();
    }

    /**
     * @param string|null $status
     * @param array       $attributes
     *
     * @return array
     */
    protected function fakeJobsWithStatus(string $status = null, array $attributes = []): array
    {
        $count = $this->faker->numberBetween(1, 5);

        $jobs = [];
        for ($i = 0; $i < $count; ++$i) {
            $jobs[] = $this->fakeJobWithStatus($status, $attributes);
        }

        return $jobs;
    }
}
