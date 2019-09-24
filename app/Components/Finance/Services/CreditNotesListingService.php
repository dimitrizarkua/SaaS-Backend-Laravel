<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Interfaces\CreditNoteCountersDataProviderInterface;
use App\Components\Finance\Interfaces\CreditNoteListingDataProviderInterface;
use App\Components\Finance\Interfaces\CreditNotesListingServiceInterface;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class CreditNotesListingService
 *
 * @package App\Components\Finance\Services
 */
class CreditNotesListingService implements CreditNotesListingServiceInterface
{
    /**
     * @var CreditNoteListingDataProviderInterface
     */
    private $listingDataProvider;

    /**
     * @var CreditNoteCountersDataProviderInterface
     */
    private $countersDataProvider;

    /**
     * CreditNotesListingService constructor.
     *
     * @param CreditNoteListingDataProviderInterface $listingDataProvider
     */
    public function __construct(
        CreditNoteListingDataProviderInterface $listingDataProvider,
        CreditNoteCountersDataProviderInterface $countersDataProvider
    ) {
        $this->listingDataProvider  = $listingDataProvider;
        $this->countersDataProvider = $countersDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function getInfo(array $locationIds): array
    {
        $result = [
            'draft'            => $this->countersDataProvider->getDraftCounter($locationIds)->toArray(),
            'pending_approval' => $this->countersDataProvider->getPendingApprovalCounters($locationIds)->toArray(),
            'approved'         => $this->countersDataProvider->getApprovedCounters($locationIds)->toArray(),
        ];

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getDraft(Filter $filter): Collection
    {
        return $this->listingDataProvider->getDraft($filter);
    }

    /**
     * @inheritdoc
     */
    public function getPendingApproval(Filter $filter): Collection
    {
        return $this->listingDataProvider->getPendingApproval($filter);
    }

    /**
     * @inheritdoc
     */
    public function getApproved(Filter $filter): Collection
    {
        return $this->listingDataProvider->getApproved($filter);
    }

    /**
     * @inheritdoc
     */
    public function getAll(Filter $filter): Builder
    {
        return $this->listingDataProvider->getAll($filter);
    }
}
