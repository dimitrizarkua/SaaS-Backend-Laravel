<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Interfaces\CreditNoteListingDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingDataProviderInterface;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\FinancialEntityItem;
use App\Models\Filter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class GSTReportService
 *
 * @package App\Components\Reporting\Services
 */
class GSTReportService
{
    /**
     * @var InvoiceListingDataProviderInterface
     */
    private $invoiceDataProvider;
    /**
     * @var CreditNoteListingDataProviderInterface
     */
    private $creditNoteDataProvider;
    /**
     * @var PurchaseOrderListingDataProviderInterface
     */
    private $purchaseOrderDataProvider;

    /**
     * GSTReportService constructor.
     *
     * @param InvoiceListingDataProviderInterface       $invoiceDataProvider
     * @param CreditNoteListingDataProviderInterface    $creditNoteDataProvider
     * @param PurchaseOrderListingDataProviderInterface $purchaseOrderDataProvider
     */
    public function __construct(
        InvoiceListingDataProviderInterface $invoiceDataProvider,
        CreditNoteListingDataProviderInterface $creditNoteDataProvider,
        PurchaseOrderListingDataProviderInterface $purchaseOrderDataProvider
    ) {
        $this->invoiceDataProvider       = $invoiceDataProvider;
        $this->creditNoteDataProvider    = $creditNoteDataProvider;
        $this->purchaseOrderDataProvider = $purchaseOrderDataProvider;
    }

    /**
     * Returns report data.
     *
     * @param Filter $filter
     *
     * @return Collection
     */
    public function getReport(Filter $filter): Collection
    {
        $invoices       = $this->invoiceDataProvider->getApproved($filter);
        $creditNotes    = $this->creditNoteDataProvider->getApproved($filter);
        $purchaseOrders = $this->purchaseOrderDataProvider->getApproved($filter);

        $reducer = function (Collection $itemsCollection, FinancialEntity $entity) {
            return $itemsCollection->merge($entity->items);
        };

        return collect()
            ->merge($invoices->reduce($reducer, collect()))
            ->merge($creditNotes->reduce($reducer, collect()))
            ->merge($purchaseOrders->reduce($reducer, collect()))
            ->groupBy(function (FinancialEntityItem $item) {
                return $item->glAccount->accountType->increase_action_is_debit ? 'expenses' : 'income';
            })
            ->map(function (Collection $groupByAccountType) {
                return $groupByAccountType
                    ->groupBy(function (FinancialEntityItem $item) {
                        return Str::snake(Str::lower($item->taxRate->name));
                    })
                    ->map(function (Collection $groupByTax) {
                        $initial = [
                            'total_amount' => 0,
                            'taxes'        => 0,
                        ];

                        $result = $groupByTax->reduce(function (array $output, FinancialEntityItem $item) {
                            return [
                                'total_amount' => $output['total_amount'] + $item->getTotalAmount(),
                                'taxes'        => $output['taxes'] + $item->getItemTax(),
                            ];
                        }, $initial);

                        $result['tax_rate'] = $groupByTax->first()->taxRate->rate * 100;
                        $result['name']     = $groupByTax->first()->taxRate->name;

                        return $result;
                    });
            })
            ->map(function (Collection $groupByAccountType) {
                return [
                    'data'  => array_values($groupByAccountType->toArray()),
                    'total' => $groupByAccountType->sum('total_amount'),
                ];
            });
    }
}
