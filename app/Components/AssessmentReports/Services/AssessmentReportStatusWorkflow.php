<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Interfaces\AssessmentReportStatusWorkflowInterface;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\StatusWorkflowInterface;

/**
 * Class AssessmentReportStatusWorkflow
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportStatusWorkflow implements AssessmentReportStatusWorkflowInterface
{
    /** @var array $transitions */
    private $transitions = [];

    /** @var AssessmentReport $assessmentReport */
    private $assessmentReport;

    /**
     * AssessmentReportStatusWorkflow constructor.
     */
    public function __construct()
    {
        foreach (AssessmentReportStatuses::values() as $status) {
            $this->addStatus($status);
        }

        $this->addTransitions(AssessmentReportStatuses::DRAFT, [
            AssessmentReportStatuses::PENDING_CLIENT_APPROVAL,
            AssessmentReportStatuses::CANCELLED,
        ]);

        $this->addTransitions(AssessmentReportStatuses::PENDING_CLIENT_APPROVAL, [
            AssessmentReportStatuses::CLIENT_APPROVED,
            AssessmentReportStatuses::CLIENT_CANCELLED,
            AssessmentReportStatuses::CANCELLED,
        ]);

        $this->addTransitions(AssessmentReportStatuses::CLIENT_CANCELLED, [
            AssessmentReportStatuses::DRAFT,
        ]);

        $this->addTransitions(AssessmentReportStatuses::CANCELLED, [
            AssessmentReportStatuses::DRAFT,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setAssessmentReport(AssessmentReport $assessmentReport): AssessmentReportStatusWorkflowInterface
    {
        $this->assessmentReport = $assessmentReport;

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

        $this->assessmentReport->changeStatus($status, $userId);

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
        if (!$this->assessmentReport) {
            throw new \RuntimeException('Assessment report is not set.');
        }

        return $this->assessmentReport->latestStatus->status;
    }
}
