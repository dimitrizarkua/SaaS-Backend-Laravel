<?php

namespace App\Console\Commands;

use App\Components\Finance\DataProviders\CountersDataProvider;
use App\Components\Finance\Interfaces\CreditNoteCountersDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Locations\Models\Location;
use Illuminate\Console\Command;

/**
 * Class RecalculateFinancialCountersCommand
 *
 * @package App\Console\Commands
 */
class RecalculateFinancialCountersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:recalculate_counters 
    {--locations=* : specify locations id for recalculation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate financial counters for invoice, credit notes and purchase orders.';

    /**
     * @var InvoiceCountersDataProviderInterface|CountersDataProvider
     */
    private $invoiceDataProvider;

    /**
     * @var PurchaseOrderCountersDataProviderInterface|CountersDataProvider
     */
    private $purchaseOrderDataProvider;

    /**
     * @var CreditNoteCountersDataProviderInterface|CountersDataProvider
     */
    private $creditNoteDataProvider;

    /**
     * RecalculateFinancialCountersCommand constructor.
     *
     * @param InvoiceCountersDataProviderInterface       $invoiceDataProvider
     * @param PurchaseOrderCountersDataProviderInterface $purchaseOrderDataProvider
     * @param CreditNoteCountersDataProviderInterface    $creditNoteDataProvider
     */
    public function __construct(
        InvoiceCountersDataProviderInterface $invoiceDataProvider,
        PurchaseOrderCountersDataProviderInterface $purchaseOrderDataProvider,
        CreditNoteCountersDataProviderInterface $creditNoteDataProvider
    ) {
        parent::__construct();
        $this->invoiceDataProvider       = $invoiceDataProvider;
        $this->purchaseOrderDataProvider = $purchaseOrderDataProvider;
        $this->creditNoteDataProvider    = $creditNoteDataProvider;
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function handle(): void
    {

        $locations = $this->option('locations');

        if (null === $locations) {
            $locations = Location::all()
                ->pluck('id')
                ->toArray();
        } else {
            $this->validateLocations($locations);
        }

        $this->invoiceDataProvider->recalculateCounters($locations);
        $this->purchaseOrderDataProvider->recalculateCounters($locations);
        $this->creditNoteDataProvider->recalculateCounters($locations);
    }

    /**
     * Validates given location ids.
     *
     * @param array $locations
     */
    private function validateLocations(array $locations): void
    {
        $locationModels = Location::whereIn('id', $locations)
            ->get()
            ->keyBy('id');

        $failed = false;
        foreach ($locations as $locationId) {
            if (!$locationModels->has($locationId)) {
                $this->error(sprintf('Invalid location [ID:%d]', $locationId));
                $failed = true;
            }
        }

        if ($failed) {
            exit(1);
        }
    }
}
