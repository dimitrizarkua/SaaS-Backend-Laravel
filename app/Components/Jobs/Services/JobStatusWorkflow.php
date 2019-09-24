<?php

namespace App\Components\Jobs\Services;

use App\Components\Finance\Models\Invoice;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobStatusChanged;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobStatusWorkflowInterface;
use App\Components\Jobs\Interfaces\StatusWorkflowInterface;
use App\Components\Jobs\Models\Job;
use Illuminate\Support\Facades\DB;

/**
 * Class JobStatusWorkflow
 *
 * @package App\Components\Jobs\Services
 */
class JobStatusWorkflow implements JobStatusWorkflowInterface
{
    /** @var array $transitions */
    private $transitions = [];

    /** @var \App\Components\Jobs\Models\Job $job */
    private $job;

    /**
     * JobStatusWorkflow constructor.
     */
    public function __construct()
    {
        foreach (JobStatuses::values() as $status) {
            $this->addStatus($status);
        }

        $this->addTransitions(JobStatuses::NEW, [
            JobStatuses::ON_HOLD,
            JobStatuses::IN_PROGRESS,
            JobStatuses::CANCELLED,
            JobStatuses::CLOSED,
        ]);
        $this->addTransitions(JobStatuses::ON_HOLD, [
            JobStatuses::NEW,
            JobStatuses::IN_PROGRESS,
            JobStatuses::CANCELLED,
            JobStatuses::CLOSED,
        ]);
        $this->addTransitions(JobStatuses::IN_PROGRESS, [
            JobStatuses::CANCELLED,
            JobStatuses::CLOSED,
        ]);
        $this->addTransitions(JobStatuses::CLOSED, [
            JobStatuses::NEW,
            JobStatuses::IN_PROGRESS,
            JobStatuses::ON_HOLD,
        ]);
        $this->addTransitions(JobStatuses::CANCELLED, [
            JobStatuses::NEW,
            JobStatuses::IN_PROGRESS,
            JobStatuses::ON_HOLD,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setJob(Job $job): JobStatusWorkflowInterface
    {
        $this->job = $job;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addStatus(string $status): StatusWorkflowInterface
    {
        $this->transitions[$status] = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function addTransition(string $from, string $to): StatusWorkflowInterface
    {
        if (!isset($this->transitions[$from])) {
            throw new NotAllowedException(sprintf(
                'Requested status %s is not listed as the status from which transition is possible.',
                $from
            ));
        }

        $this->transitions[$from][] = $to;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function addTransitions(string $from, array $toStates): StatusWorkflowInterface
    {
        foreach ($toStates as $to) {
            $this->addTransition($from, $to);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function changeStatus(string $status, string $note = null, int $userId = null): StatusWorkflowInterface
    {
        if (!in_array($status, $this->getNextStatuses())) {
            throw new NotAllowedException(sprintf('Could not change status to %s', $status));
        }

        $isClosing = in_array($status, JobStatuses::$closedStatuses, true);
        if (true === $isClosing) {
            /** @var Invoice[] $invoices */
            $invoices = Invoice::query()
                ->where('job_id', $this->job->id)
                ->get();

            foreach ($invoices as $invoice) {
                $notPaid = [];
                if (false === $invoice->isPaidInFull()) {
                    $notPaid[] = $invoice->id;
                }
            }
            if (!empty($notPaid)) {
                $message = sprintf(
                    'The job has unpaid invoice(s): %s',
                    implode(', ', $notPaid)
                );
                throw new NotAllowedException($message);
            }
        }

        DB::transaction(function () use ($status, $note, $userId) {
            $this->job->changeStatus($status, $note, $userId);
            $this->job->updateTouchedAt();
        });

        event(new JobUpdated($this->job, $userId));
        event(new JobStatusChanged($this->job, $userId));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getNextStatuses(): array
    {
        $currentStatus = $this->getCurrentStatus();

        return isset($this->transitions[$currentStatus]) ? $this->transitions[$currentStatus] : [];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getCurrentStatus(): string
    {
        if (!$this->job) {
            throw new \RuntimeException('Job not set');
        }

        return $this->job->latestStatus->status;
    }
}
