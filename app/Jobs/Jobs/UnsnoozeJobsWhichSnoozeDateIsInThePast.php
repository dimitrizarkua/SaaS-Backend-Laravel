<?php

namespace App\Jobs\Jobs;

use App\Components\Jobs\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class UnsnoozeJobsWhichSnoozeDateIsInThePast
 *
 * @package App\Jobs\Jobs
 */
class UnsnoozeJobsWhichSnoozeDateIsInThePast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Job::shouldBeUnsnoozed()->update([
            'snoozed_until' => null,
        ]);
    }
}
