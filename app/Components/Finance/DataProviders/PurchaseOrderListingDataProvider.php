<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\PurchaseOrderListingDataProviderInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Models\Filter;
use App\Models\HasLatestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Class PurchaseOrderListingDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class PurchaseOrderListingDataProvider implements PurchaseOrderListingDataProviderInterface
{
    use HasLatestStatus;

    /**
     * @inheritDoc
     */
    public function getAll(Filter $filter): Builder
    {
        return $this->getBaseQuery($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getDraft(Filter $filter): Collection
    {
        return $this->getDraftPurchaseOrdersQuery($filter)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingApproval(Filter $filter): Collection
    {
        return $this->getPendingApprovalPurchaseOrdersQuery($filter)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getApproved(Filter $filter): Collection
    {
        return $this->getApprovedPurchaseOrdersQuery($filter)
            ->get();
    }

    /**
     * Returns query builder for draft purchase orders.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getDraftPurchaseOrdersQuery(Filter $filter)
    {
        $query = $this->getBaseQuery($filter)
            ->where(function (Builder $query) {
                $query->whereNull('locked_at')
                    ->orWhere(function (Builder $query) {
                        $query->whereNotExists(function (QueryBuilder $query) {
                            $query->select('purchase_order_id')
                                ->from('purchase_order_approve_requests')
                                ->whereRaw('purchase_order_approve_requests.purchase_order_id = purchase_orders.id');
                        })
                            ->whereIn(
                                'purchase_orders.id',
                                $this->getEntityIdsWhereLatestStatusIs(
                                    'purchase_orders',
                                    [FinancialEntityStatuses::DRAFT]
                                )
                            )
                            ->get();
                    });
            });

        return $query;
    }

    /**
     * Returns query builder for pending approval purchase orders.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getPendingApprovalPurchaseOrdersQuery(Filter $filter)
    {
        $query = $this->getBaseQuery($filter)
            ->whereNotNull('locked_at')
            ->whereExists(function (QueryBuilder $query) {
                $query->select('purchase_order_id')
                    ->from('purchase_order_approve_requests')
                    ->whereRaw('purchase_order_approve_requests.purchase_order_id = purchase_orders.id')
                    ->whereNull('approved_at');
            });

        return $query;
    }

    /**
     * Returns query builder for approved purchase orders.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getApprovedPurchaseOrdersQuery(Filter $filter)
    {
        $query = $this->getBaseQuery($filter)
            ->whereNotNull('locked_at')
            ->whereExists(function (QueryBuilder $query) {
                $query->select('purchase_order_id')
                    ->from('purchase_order_statuses')
                    ->whereRaw('purchase_order_statuses.purchase_order_id = purchase_orders.id')
                    ->whereIn(
                        'purchase_orders.id',
                        $this->getEntityIdsWhereLatestStatusIs('purchase_orders', [FinancialEntityStatuses::APPROVED])
                    );
            });

        return $query;
    }

    /**
     * @return array
     */
    private function getFieldsForListing(): array
    {
        return [
            'purchase_orders.id',
            'location_id',
            'accounting_organization_id',
            'recipient_contact_id',
            'recipient_name',
            'recipient_address',
            'job_id',
            'date',
            'reference',
            $this->getPurchaseOrderTotalCost(),
        ];
    }

    /**
     * Returns query expression for calculating total amount of purchase order item.
     *
     * @return \Illuminate\Database\Query\Expression
     */
    private function getPurchaseOrderTotalCost()
    {
        return \DB::raw('
            SUM(
                purchase_order_items.unit_cost
                * purchase_order_items.quantity
                * (1 + (purchase_order_items.markup / 100))
            ) AS total_amount
        ');
    }

    /**
     * Returns base query to retrieve available purchase orders for given locations.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getBaseQuery(Filter $filter)
    {
        $query = PurchaseOrder::query()
            ->leftJoin(
                'purchase_order_items',
                'purchase_orders.id',
                '=',
                'purchase_order_items.purchase_order_id'
            )
            ->select($this->getFieldsForListing())
            ->groupBy('purchase_orders.id')
            ->orderByDesc('id');

        return $filter->apply($query);
    }
}
