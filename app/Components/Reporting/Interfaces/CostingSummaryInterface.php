<?php

namespace App\Components\Reporting\Interfaces;

/**
 * Interface CostingSummaryInterface
 *
 * @package App\Components\Reporting\Interfaces
 */
interface CostingSummaryInterface
{
    /**
     * @param int $jobId
     *
     * @return array
     */
    public function getSummary(int $jobId): array;
}
