<?php

namespace App\Components\Reporting\Models;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use Illuminate\Support\Facades\DB;

/**
 * Trait UsageCosts
 *
 * @package App\Components\Reporting\Models
 */
trait UsageCosts
{
    /**
     * Returns total cost of labour for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getLaboursTotalCost(array $jobIds): float
    {
        return JobLabour::query()
            ->whereIn('job_id', $jobIds)
            ->get()
            ->reduce(function ($labourTotalCost, JobLabour $jobLabour) {
                return $labourTotalCost + $jobLabour->calculateTotalAmount();
            }, 0);
    }

    /**
     * Returns total cost of laha compensations for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getLahaCompensationTotalCost(array $jobIds): float
    {
        return (float)JobLahaCompensation::query()
            ->whereIn('job_id', $jobIds)
            ->sum(DB::raw('rate_per_day * days'));
    }

    /**
     * Returns total cost of allowances for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getAllowancesTotalCost(array $jobIds): float
    {
        return (float)JobAllowance::query()
            ->whereIn('job_id', $jobIds)
            ->sum(DB::raw('charge_rate_per_interval * amount'));
    }

    /**
     * Returns total cost of reimbursements for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getReimbursementTotalCost(array $jobIds): float
    {
        return (float)JobReimbursement::query()
            ->whereIn('job_id', $jobIds)
            ->where('is_chargeable', '=', true)
            ->sum('total_amount');
    }

    /**
     * Returns total cost of materials for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getMaterialTotalCost(array $jobIds): float
    {
        return (float)JobMaterial::query()
            ->whereIn('job_id', $jobIds)
            ->sum(DB::raw('buy_cost_per_unit * quantity_used_override'));
    }

    /**
     * Returns total cost of equipment for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getEquipmentTotalCost(array $jobIds): float
    {
        return (float)JobEquipment::query()
            ->whereNotNull('ended_at')
            ->whereIn('job_id', $jobIds)
            ->sum(DB::raw('buy_cost_per_interval * intervals_count_override'));
    }

    /**
     * Returns amount of list of approved assessment reports for listed jobs.
     *
     * @param array $jobIds list of job identifiers.
     *
     * @return float
     */
    protected function getAssessmentReportsAmount(array $jobIds): float
    {
        return Job::whereIn('id', $jobIds)
            ->with('assessmentReports')
            ->get()
            ->reduce(function (float $carry, Job $job) {
                return $carry + $job->assessmentReports->reduce(function (float $carry, AssessmentReport $report) {
                        return $report->isApproved()
                            ? $carry + $report->getTotalAmount() + $report->getTax()
                            : $carry;
                }, 0);
            }, 0);
    }
}
