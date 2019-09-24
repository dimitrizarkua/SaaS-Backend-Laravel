<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Jobs\Models\JobTask;
use App\Jobs\Jobs\UnsnoozeJobTasksWhichSnoozeDateIsInThePast;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class UnsnoozeJobTasksTest
 *
 * @package Tests\Unit\LaravelJobs\Jobs
 */
class UnsnoozeJobTasksTest extends TestCase
{
    public function testUnsnoozeJobTasksWhichSnoozeDateIsInThePast(): void
    {
        $count = $this->faker->numberBetween(1, 3);
        factory(JobTask::class, $count)->create([
            'snoozed_until' => Carbon::now()->subMinutes(5),
        ]);

        $snoozedTasks = JobTask::query()
            ->whereNotNull('snoozed_until')
            ->get();
        self::assertCount($count, $snoozedTasks);

        (new UnsnoozeJobTasksWhichSnoozeDateIsInThePast())->handle();

        $unsnoozedTasks = JobTask::query()
            ->whereNull('snoozed_until')
            ->get();
        self::assertCount($count, $unsnoozedTasks);
    }

    public function testDoNotUnsnoozeJobTasksWhichSnoozeDateIsInTheFuture(): void
    {
        $count = $this->faker->numberBetween(1, 3);
        factory(JobTask::class, $count)->create([
            'snoozed_until' => Carbon::now()->addMinutes(5),
        ]);

        $snoozedTasks = JobTask::query()
            ->whereNotNull('snoozed_until')
            ->get();
        self::assertCount($count, $snoozedTasks);

        (new UnsnoozeJobTasksWhichSnoozeDateIsInThePast())->handle();

        $unsnoozedTasks = JobTask::query()
            ->whereNull('snoozed_until')
            ->get();
        self::assertCount(0, $unsnoozedTasks);
    }
}
