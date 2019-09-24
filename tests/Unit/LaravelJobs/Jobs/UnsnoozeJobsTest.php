<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Jobs\Models\Job;
use App\Jobs\Jobs\UnsnoozeJobsWhichSnoozeDateIsInThePast;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class UnsnoozeJobsTest
 *
 * @package Tests\Unit\LaravelJobs\Jobs
 */
class UnsnoozeJobsTest extends TestCase
{
    public function testUnsnoozeJobsWhichSnoozeDateIsInThePast(): void
    {
        $count = $this->faker->numberBetween(1, 3);
        factory(Job::class, $count)->create([
            'snoozed_until' => Carbon::now()->subMinutes(5),
        ]);

        $snoozedJobs = Job::query()
            ->whereNotNull('snoozed_until')
            ->get();
        self::assertCount($count, $snoozedJobs);

        (new UnsnoozeJobsWhichSnoozeDateIsInThePast())->handle();

        $unsnoozedJobs = Job::query()
            ->whereNull('snoozed_until')
            ->get();
        self::assertCount($count, $unsnoozedJobs);
    }

    public function testDoNotUnsnoozeJobsWhichSnoozeDateIsInTheFuture(): void
    {
        $count = $this->faker->numberBetween(1, 3);
        factory(Job::class, $count)->create([
            'snoozed_until' => Carbon::now()->addMinutes(5),
        ]);

        $snoozedJobs = Job::query()
            ->whereNotNull('snoozed_until')
            ->get();
        self::assertCount($count, $snoozedJobs);

        (new UnsnoozeJobsWhichSnoozeDateIsInThePast())->handle();

        $unsnoozedJobs = Job::query()
            ->whereNull('snoozed_until')
            ->get();
        self::assertCount(0, $unsnoozedJobs);
    }
}
