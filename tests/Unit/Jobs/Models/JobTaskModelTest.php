<?php

namespace Tests\Unit\Jobs\Models;

use App\Components\Jobs\Models\JobTask;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class JobTaskModelTest
 *
 * @package Tests\Unit\Jobs\Models
 * @group   jobs
 * @group   job-tasks
 */
class JobTaskModelTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testKpiMissedAtShouldBeProtected()
    {
        /** @var JobTask $task */
        $task = factory(JobTask::class)->create();

        $firstValue          = Carbon::now()->second(0);
        $task->kpi_missed_at = $firstValue;
        $task->saveOrFail();

        $task->kpi_missed_at = Carbon::now()->addDays(1);
        self::assertEquals($firstValue, $task->kpi_missed_at);
    }
}
