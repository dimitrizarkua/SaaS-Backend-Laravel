<?php

namespace App\Jobs\RecurringJobs;

use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\Jobs\Models\VO\JobCreationData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

/**
 * Class CreateJobFromRecurringJob
 *
 * @package App\Jobs\RecurringJobs
 */
class CreateJobFromRecurringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    protected $recurringJobId;

    /** @var Carbon */
    protected $startDate;

    /**
     * CreateJobFromRecurringJob constructor.
     *
     * @param int    $recurringJobId
     * @param Carbon $startDate
     */
    public function __construct(int $recurringJobId, Carbon $startDate)
    {
        $this->recurringJobId = $recurringJobId;
        $this->startDate      = $startDate;
    }

    /**
     * @return int
     */
    public function getRecurringJobId()
    {
        return $this->recurringJobId;
    }

    /**
     * @param \App\Components\Jobs\Interfaces\JobsServiceInterface $jobsService
     */
    public function handle(JobsServiceInterface $jobsService)
    {
        Log::info('CreateJobFromRecurringJob started');

        /**
         * For the last occurrence of the job, the job could have been already (soft-)deleted but we will still
         * need to create a job.
         */
        $recurringJob = RecurringJob::withTrashed()
            ->find($this->recurringJobId);

        if (null === $recurringJob) {
            Log::notice(
                sprintf('Recurring job [JOB_ID:%s] does not exists', $recurringJob->id),
                ['recurring_job_id' => $recurringJob->id]
            );

            return;
        }

        /** @var RecurringJob $recurringJob */
        $recurringJobData                     = $recurringJob->toArray();
        $recurringJobData['recurring_job_id'] = $recurringJobData['id'];
        try {
            $jobData         = new JobCreationData($recurringJobData);
            $job             = $jobsService->createJob($jobData);
            $job->created_at = $this->startDate;
            $job->save();
        } catch (Exception | Throwable $e) {
            Log::alert(
                sprintf(
                    'Creation job from recurring error: %s. Trace: %s',
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                $recurringJobData
            );
        }
        Log::info('CreateJobFromRecurringJob finished');
    }
}
