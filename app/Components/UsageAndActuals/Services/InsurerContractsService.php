<?php

namespace App\Components\UsageAndActuals\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\UsageAndActuals\Exceptions\NotAllowedException;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Components\UsageAndActuals\Models\InsurerContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class InsurerContractsService
 *
 * @package App\Components\UsageAndActuals\Services
 */
class InsurerContractsService implements InsurerContractsInterface
{
    /**
     * {@inheritdoc}
     */
    public function createContract(InsurerContractData $contractData): InsurerContract
    {
        $isOverlapped = $this->isOverlapped(
            $contractData->getContactId(),
            $contractData->getEffectDate(),
            $contractData->getTerminationDate()
        );

        if ($isOverlapped) {
            throw new NotAllowedException('New contract time overlaps with existing contracts for the insurer');
        }

        $insurerContract = new InsurerContract([
            'contact_id'       => $contractData->getContactId(),
            'contract_number'  => $contractData->getContractNumber(),
            'description'      => $contractData->getDescription(),
            'effect_date'      => $contractData->getEffectDate(),
            'termination_date' => $contractData->getTerminationDate(),
        ]);

        $insurerContract->saveOrFail();

        return $insurerContract;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContract(InsurerContract $insurerContract, InsurerContractData $contractData): InsurerContract
    {
        $isOverlapped = $this->isOverlapped(
            $contractData->getContactId() ?? $insurerContract->contact_id,
            $contractData->getEffectDate(),
            $contractData->getTerminationDate(),
            $insurerContract->id
        );

        if ($isOverlapped) {
            throw new NotAllowedException('New contract time overlaps with existing contracts for the insurer');
        }

        $insurerContract->update($contractData->toArray());

        return $insurerContract;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContract(int $insurerContractId): void
    {
        $insurerContract = $this->getContract($insurerContractId);

        try {
            $insurerContract->delete();
        } catch (\Exception $exception) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContract(int $insurerContractId): InsurerContract
    {
        return InsurerContract::findOrFail($insurerContractId);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveContractForInsurer(Contact $insurer): ?InsurerContract
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return InsurerContract::query()
            ->where('contact_id', $insurer->id)
            ->whereDate('effect_date', '<=', $currentDate)
            ->where(function (Builder $q) use ($currentDate) {
                $q->whereDate('termination_date', '>=', $currentDate)
                    ->orWhereNull('termination_date');
            })
            ->first();
    }

    /**
     * Returns is contract period overlaps with existing contracts for the insurer.
     *
     * @param int                 $insurerId
     * @param \Carbon\Carbon|null $effectDate
     * @param \Carbon\Carbon|null $terminationDate
     * @param int|null            $contractId
     *
     * @return bool
     */
    private function isOverlapped(
        int $insurerId,
        Carbon $effectDate = null,
        Carbon $terminationDate = null,
        int $contractId = null
    ) {
        if (is_null($effectDate) && is_null($terminationDate)) {
            return false;
        }

        return InsurerContract::query()
            ->where('contact_id', $insurerId)
            ->when($contractId, function (Builder $query) use ($contractId) {
                return $query->where('id', '!=', $contractId);
            })
            ->when($effectDate, function (Builder $query) use ($effectDate) {
                return $query->whereDate('termination_date', '>=', $effectDate)
                    ->orWhereNull('termination_date');
            })
            ->when($terminationDate, function (Builder $query) use ($terminationDate) {
                return $query->whereDate('effect_date', '<=', $terminationDate);
            })
            ->exists();
    }
}
