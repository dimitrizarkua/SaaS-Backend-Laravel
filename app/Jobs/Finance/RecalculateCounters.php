<?php

namespace App\Jobs\Finance;

use App\Components\Finance\DataProviders\CountersDataProvider;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RecalculateCounters
 *
 * @package App\Jobs\Finance
 */
class RecalculateCounters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int[]
     */
    private $locationIds;

    /**
     * @var CountersDataProvider
     */
    private $dataProvider;

    /**
     * RecalculateCounters constructor.
     *
     * @param int $locationId
     */
    public function __construct(CountersDataProvider $dataProvider, array $locationIds)
    {
        $this->locationIds  = $locationIds;
        $this->dataProvider = $dataProvider;
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function handle(): void
    {
        $this->dataProvider->recalculateCounters($this->locationIds);
    }
}
