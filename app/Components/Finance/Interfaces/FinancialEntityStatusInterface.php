<?php

namespace App\Components\Finance\Interfaces;

/**
 * Interface FinancialEntityStatusInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface FinancialEntityStatusInterface
{
    /**
     * Checks whether status can be changed to new one.
     *
     * @param string $newStatus New status.
     *
     * @return bool
     */
    public function canBeChangedTo(string $newStatus): bool;

    /**
     * Returns status. Should return on of FinancialEntityStatuses const.
     *
     * @see \App\Components\Finance\Enums\FinancialEntityStatuses
     *
     * @return string
     */
    public function getStatus(): string;
}
