<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\CreditNoteListingDataProviderInterface;
use App\Components\Finance\Models\CreditNote;
use App\Models\Filter;
use App\Models\HasLatestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CreditNoteListingDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class CreditNoteListingDataProvider implements CreditNoteListingDataProviderInterface
{
    use HasLatestStatus;

    /**
     * @inheritDoc
     */
    public function getDraft(Filter $filter): Collection
    {
        return $this->getCommonQuery($filter)
            ->whereNotExists(function (QueryBuilder $query) {
                $query->select('credit_note_approve_requests.approver_id')
                    ->from('credit_note_approve_requests')
                    ->whereRaw('credit_note_approve_requests.credit_note_id = credit_notes.id');
            })
            ->whereIn(
                'credit_notes.id',
                $this->getEntityIdsWhereLatestStatusIs('credit_notes', [FinancialEntityStatuses::DRAFT])
            )
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getPendingApproval(Filter $filter): Collection
    {
        return $this->getCommonQuery($filter)
            ->whereExists(function (QueryBuilder $query) {
                $query->select('credit_note_approve_requests.approver_id')
                    ->from('credit_note_approve_requests')
                    ->whereRaw('credit_note_approve_requests.credit_note_id = credit_notes.id')
                    ->whereNull('approved_at');
            })
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getApproved(Filter $filter): Collection
    {
        return $this->getCommonQuery($filter)
            ->whereIn(
                'credit_notes.id',
                $this->getEntityIdsWhereLatestStatusIs('credit_notes', [FinancialEntityStatuses::APPROVED])
            )
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getAll(Filter $filter): Builder
    {
        return $this->getCommonQuery($filter);
    }

    /**
     * Returns common query for all methods of data provider.
     *
     * @param Filter $filter Filter instance.
     *
     * @return Builder
     */
    private function getCommonQuery(Filter $filter): Builder
    {
        $query = CreditNote::query()
            ->leftJoin(
                DB::raw(
                    '(
                        SELECT DISTINCT ON ("credit_note_id") status, credit_note_id
                        FROM "credit_note_statuses" 
                        ORDER BY  "credit_note_id", 
                        "created_at" DESC,
                        "id" DESC
                     ) AS sub'
                ),
                function (JoinClause $join) {
                    $join->on('credit_notes.id', '=', 'sub.credit_note_id');
                }
            );

        return $filter->apply($query);
    }
}
