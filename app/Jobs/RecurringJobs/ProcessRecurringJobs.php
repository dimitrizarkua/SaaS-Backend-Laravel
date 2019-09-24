<?php

namespace App\Jobs\RecurringJobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\RecurringJob;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use Recurr\Transformer\Constraint\BetweenConstraint;

/**
 * Class ProcessRecurringJobs
 * 1. Runs on schedule - once a day
 * 2. Creates job from active recurring jobs (deleted_at field is null and time to next occurrence is less than or
 * equal to 1 day.
 * 3. Checks recurring jobs that won't have occurrence in the future and deactivate (soft-delete) them.
 *
 * @package App\Jobs\RecurringJobs
 */
class ProcessRecurringJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Note: The transformer has a "virtual" limit (default 732) on the number of objects it generates. This prevents
     * the script from crashing on an infinitely recurring rule. You can change the virtual limit with an
     * ArrayTransformerConfig object that you pass to ArrayTransformer.
     */
    const RECURR_LIB_LIMIT = 732;

    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function handle()
    {
        $recurringJobs = RecurringJob::all();
        $transformer   = $this->getTransformer();

        $today = Carbon::now();

        foreach ($recurringJobs as $recurringJob) {
            /** @var RecurringJob $recurringJob */
            $rule        = new Rule($recurringJob->recurrence_rule);
            $recurrences = $this->getRecurrences($rule, $transformer);

            $isLastRecurrence = $recurrences->startsAfter($today)->isEmpty();

            $yesterday   = $today->copy()->subDay();
            $recurrences = $recurrences->startsBetween($yesterday, $today, true);

            if ($recurrences->isEmpty()) {
                Log::info(sprintf('No one recurrences for recurring job [RECURRING_JOB_ID:%d]', $recurringJob->id));
                continue;
            }

            foreach ($recurrences as $recurrence) {
                $startDateTime = Carbon::createFromFormat(
                    self::DATE_FORMAT,
                    $recurrence->getStart()->format(self::DATE_FORMAT)
                );

                if ($this->isJobExists($recurringJob, $startDateTime)) {
                    Log::info(
                        sprintf(
                            'Recurring job is already exists [RECURRING_JOB_ID:%d], [CREATED_AT: %s]',
                            $recurringJob->id,
                            $startDateTime
                        )
                    );
                    continue;
                }

                CreateJobFromRecurringJob::dispatch($recurringJob->id, $startDateTime)
                    ->onQueue('jobs');

                $this->logJobCreatedFromRecurring($recurringJob);

                if (!$isLastRecurrence) {
                    continue;
                }

                $isInfiniteJob = null === $rule->getUntil();

                if ($isInfiniteJob) {
                    $this->logRecurringJobUpdatedBecauseInfinite($recurringJob);

                    $rule->setStartDate($today, true);
                    $recurringJob->update(['recurrence_rule' => $rule->getString()]);
                } else {
                    $this->logRecurringJobDeleted($recurringJob);

                    $recurringJob->delete();
                }
            }
        }
    }

    /**
     * @param \Recurr\Rule                         $rule
     * @param \Recurr\Transformer\ArrayTransformer $transformer
     *
     * @return \Recurr\RecurrenceCollection
     *
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    private function getRecurrences(Rule $rule, ArrayTransformer $transformer): RecurrenceCollection
    {
        $constraint = null;

        if (null !== $rule->getUntil()) {
            $constraint = new BetweenConstraint($rule->getStartDate(), $rule->getUntil(), true);
        }

        return $transformer->transform($rule, $constraint);
    }

    /**
     * Monthly recurring rules. By default, if your start date is on the 29th, 30th, or 31st,
     * Recurr will skip following months that don't have at least that many days. (e.g. Jan 31 + 1 month = March)
     */
    private function getTransformer()
    {
        $transformer = new ArrayTransformer;

        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        return $transformer;
    }

    /**
     * @param \App\Components\Jobs\Models\RecurringJob $recurringJob
     *
     * @param \Carbon\Carbon                           $startDate
     *
     * @return bool
     */
    private function isJobExists(RecurringJob $recurringJob, Carbon $startDate): bool
    {
        return Job::query()
                ->whereTime('created_at', $startDate)
                ->where([
                    'recurring_job_id' => $recurringJob->id,
                ])
                ->exists();
    }

    /**
     * @param RecurringJob $recurringJob
     */
    private function logJobCreatedFromRecurring(RecurringJob $recurringJob)
    {
        Log::info(
            sprintf(
                'Recurring job [RECURRING_JOB_ID:%d] should be created from recurring job according 
                to the rule [RULE:%s]',
                $recurringJob->id,
                $recurringJob->recurrence_rule
            ),
            [
                'recurring_job_id' => $recurringJob->id,
                'recurrence_rule'  => $recurringJob->recurrence_rule,
            ]
        );
    }

    /**
     * @param RecurringJob $recurringJob
     */
    private function logRecurringJobUpdatedBecauseInfinite(RecurringJob $recurringJob)
    {
        Log::info(
            sprintf(
                'Recurring job [RECURRING_JOB_ID:%d] will be updated. It is infinite job [RULE:%s].',
                $recurringJob->id,
                $recurringJob->recurrence_rule
            ),
            [
                'recurring_job_id' => $recurringJob->id,
                'recurrence_rule'  => $recurringJob->recurrence_rule,
            ]
        );
    }

    /**
     * @param RecurringJob $recurringJob
     */
    private function logRecurringJobDeleted(RecurringJob $recurringJob)
    {
        Log::info(
            sprintf(
                'Recurring job [RECURRING_JOB_ID:%d] has been deleted because of won\'t have occurrence in the 
                future [RULE:%s].',
                $recurringJob->id,
                $recurringJob->recurrence_rule
            ),
            [
                'recurring_job_id' => $recurringJob->id,
                'recurrence_rule'  => $recurringJob->recurrence_rule,
            ]
        );
    }
}
