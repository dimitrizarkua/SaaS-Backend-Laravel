<?php

namespace App\Components\UsageAndActuals\Interfaces;

use App\Components\Contacts\Models\Contact;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Components\UsageAndActuals\Models\InsurerContract;

/**
 * Interface InsurerContractsInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface InsurerContractsInterface
{
    /**
     * @param \App\Components\UsageAndActuals\Models\VO\InsurerContractData $contractData
     *
     * @return \App\Components\UsageAndActuals\Models\InsurerContract
     */
    public function createContract(InsurerContractData $contractData): InsurerContract;

    /**
     * @param \App\Components\UsageAndActuals\Models\InsurerContract        $insurerContract
     * @param \App\Components\UsageAndActuals\Models\VO\InsurerContractData $data
     *
     * @return \App\Components\UsageAndActuals\Models\InsurerContract
     */
    public function updateContract(InsurerContract $insurerContract, InsurerContractData $data): InsurerContract;

    /**
     * @param int $insurerContractId
     */
    public function deleteContract(int $insurerContractId): void;

    /**
     * @param int $insurerContractId
     *
     * @return \App\Components\UsageAndActuals\Models\InsurerContract
     */
    public function getContract(int $insurerContractId): InsurerContract;

    /**
     * @param \App\Components\Contacts\Models\Contact $insurer
     *
     * @return \App\Components\UsageAndActuals\Models\InsurerContract|null
     */
    public function getActiveContractForInsurer(Contact $insurer): ?InsurerContract;
}
