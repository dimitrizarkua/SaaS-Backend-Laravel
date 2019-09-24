<?php

namespace App\Components\Jobs\Services;

use App\Components\Finance\Models\Invoice;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobEquipmentServiceInterface;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Components\Jobs\Models\VO\JobEquipmentChargeData;
use App\Components\Jobs\Models\VO\CreateJobEquipmentData;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Class JobEquipmentService
 *
 * @package App\Components\Jobs\Services
 */
class JobEquipmentService extends JobsEntityService implements JobEquipmentServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getJobEquipment(int $jobEquipmentId): JobEquipment
    {
        return JobEquipment::findOrFail($jobEquipmentId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getJobEquipmentList(int $jobId): Collection
    {
        $job = $this->jobsService()->getJob($jobId);

        return $job->equipment()
            ->with([
                'equipment',
                'chargingIntervals',
            ])
            ->get();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     * @throws \RuntimeException
     */
    public function createJobEquipment(CreateJobEquipmentData $data, int $jobId, int $userId): JobEquipment
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not add equipment to the closed or cancelled job.');
        }
        if ($this->isEquipmentUsedOnSite($data->getEquipmentId())) {
            throw new NotAllowedException('Could not add equipment because it is used on site now.');
        }

        return DB::transaction(function () use ($data, $job, $userId) {
            $equipment                          = Equipment::with('category')->findOrFail($data->getEquipmentId());
            $buyCostPerInterval                 = $equipment->category->default_buy_cost_per_interval;
            $equipmentCategoryChargingIntervals = $equipment->category->chargingIntervals()
                ->with(['insurerContract'])
                ->where(function (Builder $query) use ($job) {
                    return $query->where('is_default', true)
                        ->orWhereHas('insurerContract', function (Builder $query) use ($job) {
                            return $query->where('insurer_contract_id', $job->insurer_contract_id);
                        });
                })
                ->get();

            // If there are intervals for insurer contracts we can ignore default intervals.
            if (0 !== $equipmentCategoryChargingIntervals->where('is_default', false)->count()) {
                $equipmentCategoryChargingIntervals = $equipmentCategoryChargingIntervals
                    ->reject(function (EquipmentCategoryChargingInterval $interval) {
                        return $interval->is_default;
                    });
            }

            $commonInterval = Equipment::getCommonInterval($equipmentCategoryChargingIntervals);
            if (null === $commonInterval) {
                throw new RuntimeException(
                    'Could not add equipment to the job because can\'t determine charging interval.'
                );
            }

            $jobEquipment                        = new JobEquipment($data->toArray());
            $jobEquipment->job_id                = $job->id;
            $jobEquipment->creator_id            = $userId;
            $jobEquipment->interval              = $commonInterval->charging_interval;
            $jobEquipment->buy_cost_per_interval = $buyCostPerInterval;
            if (null !== $jobEquipment->ended_at) {
                $this->calculateIntervalsCount($jobEquipment);
            }
            $jobEquipment->saveOrFail();

            $equipmentCategoryChargingIntervals
                ->each(function (EquipmentCategoryChargingInterval $interval) use ($jobEquipment) {
                    $insurerContract = $interval->insurerContract;
                    $jobEquipment->chargingIntervals()->create([
                        'equipment_category_charging_interval_id' => $interval->id,
                        'charging_interval'                       => $interval->charging_interval,
                        'charging_rate_per_interval'              => $interval->charging_rate_per_interval,
                        'max_count_to_the_next_interval'          => $interval->max_count_to_the_next_interval,
                        'up_to_amount'                            => $insurerContract
                            ? $insurerContract->up_to_amount
                            : null,
                        'up_to_interval_count'                    => $insurerContract
                            ? $insurerContract->up_to_interval_count
                            : null,
                    ]);
                });

            return $jobEquipment;
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function finishJobEquipmentUsing(int $jobEquipmentId, Carbon $endedAt): JobEquipment
    {
        $jobEquipment = $this->isJobEquipmentAllowedToEdit($jobEquipmentId);

        if (null !== $jobEquipment->ended_at) {
            throw new NotAllowedException('Could not edit job equipment ended at date because it is already set.');
        }

        if ($endedAt->lt($jobEquipment->started_at)) {
            throw new NotAllowedException('Ended at must be greater or equal than started at.');
        }

        $jobEquipment->ended_at = $endedAt;
        $this->calculateIntervalsCount($jobEquipment);
        $jobEquipment->save();

        return $jobEquipment;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function overrideJobEquipmentIntervalsCount(int $jobEquipmentId, int $count): JobEquipment
    {
        $jobEquipment = $this->isJobEquipmentAllowedToEdit($jobEquipmentId);

        if (null === $jobEquipment->ended_at) {
            throw new NotAllowedException(
                'Could not edit job equipment intervals count override because ended at date is not set.'
            );
        }

        $jobEquipment->intervals_count_override = $count;
        $jobEquipment->save();

        return $jobEquipment;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function deleteJobEquipment(int $jobEquipmentId): void
    {
        $jobEquipment = $this->isJobEquipmentAllowedToEdit($jobEquipmentId);
        $jobEquipment->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \JsonMapper_Exception
     */
    public function getJobEquipmentTotalAmount(int $jobId): array
    {
        $job                           = $this->jobsService()->getJob($jobId);
        $jobEquipment                  = $job->equipment()
            ->with(['chargingIntervals'])
            ->get();
        $jobEquipmentChargingIntervals = $jobEquipment->pluck('chargingIntervals')
            ->flatten();

        $jobEquipmentChargeData = $jobEquipmentChargingIntervals->groupBy('equipment_category_charging_interval_id')
            ->map(function (Collection $groupedIntervals) use ($jobEquipment) {
                return $groupedIntervals->reduce(
                    function (
                        JobEquipmentChargeData $chargeData,
                        JobEquipmentChargingInterval $interval
                    ) use ($jobEquipment) {
                        /** @var JobEquipment $unit */
                        $unit = $jobEquipment->where('id', $interval->job_equipment_id)
                            ->first();

                        $intervalsCount = $unit->interval === $interval->charging_interval
                            ? $unit->intervals_count_override
                            : 0;
                        $totalCharge    = $interval->charging_rate_per_interval * $intervalsCount;

                        $chargeData->incrementTotalAmount($totalCharge)
                            ->incrementIntervalsCount($intervalsCount)
                            ->setInterval($interval);

                        return $chargeData;
                    },
                    new JobEquipmentChargeData()
                );
            })->reject(function (JobEquipmentChargeData $chargeData) {
                return $chargeData->intervals_count === 0;
            });

        $amount           = 0;
        $amountForInsurer = 0;
        foreach ($jobEquipmentChargeData as $chargeData) {
            $amount           += $this->calculateAmountForChargeData($chargeData, $jobEquipmentChargingIntervals);
            $amountForInsurer += $this->calculateAmountForChargeData($chargeData, $jobEquipmentChargingIntervals, true);
        }

        return [
            'total_amount'             => $amount,
            'total_amount_for_insurer' => $amountForInsurer,
        ];
    }

    /**
     * Calculates total amount for job equipment charge data.
     *
     * @param JobEquipmentChargeData                    $chargeData
     * @param Collection|JobEquipmentChargingInterval[] $intervals
     * @param bool                                      $allowContract
     *
     * @return float
     * @throws \JsonMapper_Exception
     */
    private function calculateAmountForChargeData(
        JobEquipmentChargeData $chargeData,
        Collection $intervals,
        bool $allowContract = false
    ): float {
        $interval = $chargeData->interval;

        if ($allowContract) {
            if (null !== $interval->up_to_amount && $chargeData->total_amount > $interval->up_to_amount) {
                return $interval->up_to_amount;
            }

            if (null !== $interval->up_to_interval_count
                && $chargeData->intervals_count > $interval->up_to_interval_count
            ) {
                return $interval->up_to_interval_count * $interval->charging_rate_per_interval;
            }
        }

        if ($interval->shouldSelectOtherInterval($chargeData->intervals_count)) {
            /**
             * This is only possible when current interval is day,
             * intervals_count is more than max_count_to_the_next_interval and there is week interval.
             *
             * @var JobEquipmentChargingInterval $weekInterval
             */
            $weekInterval = $intervals->where('job_equipment_id', $interval->job_equipment_id)
                ->where('charging_interval', EquipmentCategoryChargingIntervals::WEEK)
                ->first();

            if (null === $weekInterval) {
                return $chargeData->total_amount;
            }

            $intervalsCount = ceil($chargeData->intervals_count / 7);
            $totalAmount    = $intervalsCount * $weekInterval->charging_rate_per_interval;
            $weekChargeData = new JobEquipmentChargeData();
            $weekChargeData->incrementTotalAmount($totalAmount)
                ->incrementIntervalsCount($intervalsCount)
                ->setInterval($weekInterval);

            return $this->calculateAmountForChargeData($weekChargeData, $intervals, $allowContract);
        }

        return $chargeData->total_amount;
    }

    /**
     * Calculates and sets intervals count and intervals count override for given JobEquipment based on interval,
     * started at and ended at dates.
     *
     * @param JobEquipment $jobEquipment
     */
    private function calculateIntervalsCount(JobEquipment $jobEquipment): void
    {
        $minutesPerDay       = Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY;
        $minutesPerWeek      = $minutesPerDay * Carbon::DAYS_PER_WEEK;
        $differenceInMinutes = $jobEquipment->ended_at->diffInMinutes($jobEquipment->started_at);

        if ($jobEquipment->interval === EquipmentCategoryChargingIntervals::WEEK) {
            $jobEquipment->intervals_count = ceil($differenceInMinutes / $minutesPerWeek);
        } elseif ($jobEquipment->interval === EquipmentCategoryChargingIntervals::DAY) {
            $jobEquipment->intervals_count = ceil($differenceInMinutes / $minutesPerDay);
        } elseif ($jobEquipment->interval === EquipmentCategoryChargingIntervals::HOUR) {
            $jobEquipment->intervals_count = ceil($differenceInMinutes / Carbon::MINUTES_PER_HOUR);
        } elseif ($jobEquipment->interval === EquipmentCategoryChargingIntervals::EACH) {
            $jobEquipment->intervals_count = 1;
        }

        $jobEquipment->intervals_count_override = $jobEquipment->intervals_count;
    }

    /**
     * Checks whether a job equipment is allowed to edit and returns it.
     *
     * @param int $jobEquipmentId Job equipment identifier.
     *
     * @return JobEquipment
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    private function isJobEquipmentAllowedToEdit(int $jobEquipmentId): JobEquipment
    {
        $jobEquipment = $this->getJobEquipment($jobEquipmentId);
        if (null !== $jobEquipment->invoice_item_id
            && $this->isInvoiceApproved($jobEquipment->invoiceItem->invoice_id)
        ) {
            throw new NotAllowedException('Could not edit job equipment used on the approved invoice.');
        }

        $job = $this->jobsService()->getJob($jobEquipment->job_id);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not edit job equipment used on the closed or cancelled job.');
        }

        return $jobEquipment;
    }

    /**
     * Checks whether invoice approved or not.
     *
     * @param int $invoiceId Invoice identifier.
     *
     * @return bool
     */
    private function isInvoiceApproved(int $invoiceId): bool
    {
        $invoice = Invoice::findOrFail($invoiceId);

        return $invoice->isApproved();
    }

    /**
     * Checks whether equipment is used on site or not.
     *
     * @param int $equipmentId Equipment identifier.
     *
     * @return bool
     */
    private function isEquipmentUsedOnSite(int $equipmentId): bool
    {
        $usedJobEquipment = JobEquipment::query()
            ->where('equipment_id', $equipmentId)
            ->whereNull('ended_at')
            ->first();

        return null !== $usedJobEquipment;
    }
}
