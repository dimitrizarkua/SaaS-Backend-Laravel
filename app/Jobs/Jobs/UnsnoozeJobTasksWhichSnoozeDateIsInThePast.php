<?php

namespace App\Jobs\Jobs;

use App\Components\Jobs\Models\JobTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class UnsnoozeJobTasksWhichSnoozeDateIsInThePast
 *
 * @package App\Jobs\Jobs
 */
class UnsnoozeJobTasksWhichSnoozeDateIsInThePast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        JobTask::shouldBeUnsnoozed()->update([
            'snoozed_until' => null,
        ]);
    }
}
