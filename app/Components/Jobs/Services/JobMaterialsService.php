<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobMaterialsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\VO\JobMaterialData;
use App\Components\UsageAndActuals\Models\InsurerContractMaterial;

/**
 * Class JobMaterialsService
 *
 * @package App\Components\Jobs\Services
 */
class JobMaterialsService extends JobsEntityService implements JobMaterialsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(JobMaterialData $data): JobMaterial
    {
        $job = $this->jobsService()->getJob($data->job_id);
        if ($job->isClosed()) {
            throw new NotAllowedException('Adding new records to materials usage to closed job is not allowed.');
        }

        $modelData                           = $data->toArray();
        $modelData['quantity_used_override'] = $modelData['quantity_used'];

        $model                     = new JobMaterial($modelData);
        $model->sell_cost_per_unit = $model->calculateSellCost();
        $model->buy_cost_per_unit  = $model->calculateBuyCost();
        $model->saveOrFail();

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function update(JobMaterial $jobMaterial, JobMaterialData $data): JobMaterial
    {
        if ($jobMaterial->job->isClosed()) {
            throw new NotAllowedException('Modification of materials usage is not allowed for closed jobs.');
        }
        if (null !== $jobMaterial->invoice_item_id &&
            $jobMaterial->getInvoice()->isApproved()
        ) {
            throw new NotAllowedException('Modification of material usage is not allowed
             because this material belongs to approved invoice.');
        }
        if (null !== $data->invoice_item_id && $data->invoiceJobId() !== $data->job_id) {
            throw new NotAllowedException('Specified invoice is not linked to the same job as job material.');
        }

        $modelData = $data->toArray();
        unset($modelData['quantity_used']);

        $jobMaterial->update($modelData);

        return $jobMaterial;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(JobMaterial $jobMaterial): void
    {
        $job = $this->jobsService()->getJob($jobMaterial->job_id);
        if ($job->isClosed()) {
            throw new NotAllowedException('Deletion of materials usage is not allowed for closed jobs.');
        }
        if (null !== $jobMaterial->invoice_item_id &&
            $jobMaterial->getInvoice()->isApproved()
        ) {
            throw new NotAllowedException('Deletion of material usage is not allowed
             because this material belongs to approved invoice.');
        }

        $jobMaterial->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTotalAmountByJob(int $jobId)
    {
        $jobMaterialGroups = JobMaterial::query()
            ->where('job_id', $jobId)
            ->get()
            ->groupBy('material_id');

        $totalAmount         = '0';
        $totalAmountOverride = '0';
        //Calculate amount of each group (grouped by material).
        foreach ($jobMaterialGroups as $material => $jobMaterialGroup) {
            $totalAmountGroup         = '0';
            $totalAmountGroupOverride = '0';

            //Calculate amount without insurer contract restrictions.
            /** @var JobMaterial $jobMaterial */
            foreach ($jobMaterialGroup as $jobMaterial) {
                $totalAmountGroup         += $jobMaterial->totalAmount();
                $totalAmountGroupOverride += $jobMaterial->totalAmountOverride();
            }

            $insurerContractMaterial = InsurerContractMaterial::query()
                ->where('insurer_contract_id', Job::find($jobId)->insurer_contract_id)
                ->where('material_id', $material)
                ->first();
            //If insurer contract for this material exists...
            if ($insurerContractMaterial) {
                //...and exists "up_to_amount" restriction. Apply restriction.
                if (null !== $insurerContractMaterial->up_to_amount) {
                    $totalAmountGroup         = min(
                        $totalAmountGroup,
                        $insurerContractMaterial->up_to_amount
                    );
                    $totalAmountGroupOverride = min(
                        $totalAmountGroupOverride,
                        $insurerContractMaterial->up_to_amount
                    );
                //...and exists "up_to_units" restriction. Apply restriction.
                } elseif (null !== $insurerContractMaterial->up_to_units) {
                    $totalAmountGroup         = min(
                        $totalAmountGroup,
                        $insurerContractMaterial->up_to_units * $insurerContractMaterial->sell_cost_per_unit
                    );
                    $totalAmountGroupOverride = min(
                        $totalAmountGroupOverride,
                        $insurerContractMaterial->up_to_units * $insurerContractMaterial->sell_cost_per_unit
                    );
                }
            }

            $totalAmount         += $totalAmountGroup;
            $totalAmountOverride += $totalAmountGroupOverride;
        }

        return [
            'total_amount'          => $totalAmount,
            'total_amount_override' => $totalAmountOverride,
        ];
    }
}
