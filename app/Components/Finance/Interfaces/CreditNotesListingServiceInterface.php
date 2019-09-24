<?php

namespace App\Components\Finance\Interfaces;

use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Interface CreditNotesListingServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface CreditNotesListingServiceInterface
{
    /**
     * Returns counters for credit notes.
     *
     * @param array $locationIds Array of location ids.
     *
     * @return array
     */
    public function getInfo(array $locationIds): array;

    /**
     * Returns collection of draft credit notes relevant for locations to which given user belongs.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDraft(Filter $filter): Collection;

    /**
     * Returns collection of pending approval credit notes relevant for locations to which given user belongs.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingApproval(Filter $filter): Collection;

    /**
     * Returns collection of approved credit notes relevant for locations to which given user belongs.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getApproved(Filter $filter): Collection;

    /**
     * Returns query that returns all credit notes relevant for locations to which given user belongs.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAll(Filter $filter): Builder;
}
