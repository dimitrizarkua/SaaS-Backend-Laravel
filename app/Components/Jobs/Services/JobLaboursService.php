<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobLabourServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\VO\JobLabourData;
use App\Components\UsageAndActuals\Models\InsurerContractLabourType;

/**
 * Class JobLaboursService
 *
 * @package App\Components\Jobs\Services
 */
class JobLaboursService extends JobsEntityService implements JobLabourServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJobLabour(JobLabourData $data): JobLabour
    {
        $job = $this->jobsService()->getJob($data->job_id);
        if ($job->isClosed()) {
            throw new NotAllowedException('Adding new labour to closed job is not allowed.');
        }

        $endedAtWithBreak = clone $data->ended_at;
        if ($data->break) {
            $endedAtWithBreak->subMinutes($data->break);
        }
        if ($data->started_at >= $endedAtWithBreak) {
            throw new NotAllowedException('Start date cannot be equal or greater than end date.');
        }

        $modelData                        = $data->toArray();
        $modelData['started_at_override'] = $modelData['started_at_override'] ?? $modelData['started_at'];
        $modelData['ended_at_override']   = $modelData['ended_at_override'] ?? $modelData['ended_at'];

        $model = new JobLabour($modelData);
        $model->updateHourlyRates();
        $model->calculated_total_amount = $model->calculateTotalAmount();

        $workHoursAmountByTiers         = $model->calculateTimeIntervals();
        $model->first_tier_time_amount  = $workHoursAmountByTiers['firstTierAmount'];
        $model->second_tier_time_amount = $workHoursAmountByTiers['secondTierAmount'];
        $model->third_tier_time_amount  = $workHoursAmountByTiers['thirdTierAmount'];
        $model->fourth_tier_time_amount = $workHoursAmountByTiers['fourthTierAmount'];

        $model->saveOrFail();

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJobLabour(JobLabour $jobLabour, JobLabourData $data): JobLabour
    {
        if ($jobLabour->job->isClosed()) {
            throw new NotAllowedException('Modification of labour is not allowed for closed jobs.');
        }
        if (null !== $jobLabour->invoice_item_id &&
            $jobLabour->getInvoice()->isApproved()
        ) {
            throw new NotAllowedException('Modification of labour is not allowed
             because this labour was used on approved invoice.');
        }
        if ($data->invoiceJobId() !== $data->job_id) {
            throw new NotAllowedException('Specified invoice is not linked to the same job as job labour.');
        }

        $endedAtWithBreak = $data->ended_at_override
            ? clone $data->ended_at_override
            : clone $jobLabour->ended_at_override;

        $break = $data->break ?? $jobLabour->break;
        if ($break) {
            $endedAtWithBreak->subMinutes($break);
        }

        $startedAt = $data->started_at_override ?? $jobLabour->started_at_override;
        if ($startedAt >= $endedAtWithBreak) {
            throw new NotAllowedException('Start date cannot be equal or greater than end date.');
        }

        $modelData = $data->toArray();
        unset($modelData['started_at'], $modelData['ended_at']);

        $jobLabour->fill($modelData);
        $jobLabour->calculated_total_amount = $jobLabour->calculateTotalAmount();

        $workHoursAmountByTiers             = $jobLabour->calculateTimeIntervals();
        $jobLabour->first_tier_time_amount  = $workHoursAmountByTiers['firstTierAmount'];
        $jobLabour->second_tier_time_amount = $workHoursAmountByTiers['secondTierAmount'];
        $jobLabour->third_tier_time_amount  = $workHoursAmountByTiers['thirdTierAmount'];
        $jobLabour->fourth_tier_time_amount = $workHoursAmountByTiers['fourthTierAmount'];

        $jobLabour->saveOrFail();

        return $jobLabour;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteJobLabour(JobLabour $jobLabour): void
    {
        $job = $this->jobsService()->getJob($jobLabour->job_id);
        if ($job->isClosed()) {
            throw new NotAllowedException('Deletion of labour is not allowed for closed jobs.');
        }
        if (null !== $jobLabour->invoice_item_id &&
            $jobLabour->getInvoice()->isApproved()
        ) {
            throw new NotAllowedException('Deletion of labour is not allowed
             because this labour was used on approved invoice.');
        }

        $jobLabour->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTotalAmountByJob(int $jobId): float
    {
        $jobLabourGroups        = JobLabour::query()
            ->where('job_id', $jobId)
            ->get()
            ->groupBy('labour_type_id');
        $jobLabourTypeIds       = $jobLabourGroups->keys()->toArray();
        $insurerContractLabours = InsurerContractLabourType::query()
            ->where('insurer_contract_id', Job::find($jobId)->insurer_contract_id)
            ->whereIn('labour_type_id', $jobLabourTypeIds)
            ->get();

        $totalAmount = 0;
        //Calculate amount of each group (grouped by labour type).
        /** @var $jobLabourGroup \Illuminate\Support\Collection */
        foreach ($jobLabourGroups as $labour => $jobLabourGroup) {
            $totalAmountGroup = 0;

            //Calculate amount without insurer contract restrictions.
            $jobLabourGroup->each(function ($jobLabour) use (&$totalAmountGroup, $jobId) {
                /** @var JobLabour $jobLabour */
                $totalAmountGroup += $jobLabour->calculated_total_amount;
            });

            $insurerContractLabour = $insurerContractLabours
                ->filter(function ($value) use ($labour) {
                    return $value->labour_type_id === $labour;
                })->first();
            //If insurer contract for this labour type exists...
            if ($insurerContractLabour) {
                //...and exists "up_to_amount" restriction. Apply restriction.
                if (null !== $insurerContractLabour->up_to_amount) {
                    $totalAmountGroup = min(
                        $totalAmountGroup,
                        $insurerContractLabour->up_to_amount
                    );
                }
                //...and exists "up_to_hours" restriction. Apply restriction.
                if (null !== $insurerContractLabour->up_to_hours) {
                    $totalAmountGroup = min(
                        $totalAmountGroup,
                        $insurerContractLabour->up_to_hours * $insurerContractLabour->first_tier_hourly_rate
                    );
                }
            }
            $totalAmount += $totalAmountGroup;
        }

        return $totalAmount;
    }
}
