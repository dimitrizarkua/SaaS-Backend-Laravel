<?php

namespace App\Jobs\Finance;

use App\Components\Finance\Services\FinancialEntityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class GeneratePDFForFinancialEntity
 *
 * @package App\Jobs\Finance
 */
class GeneratePDFForFinancialEntity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var FinancialEntityService
     */
    private $service;

    /**
     * GeneratePDFForFinancialEntity constructor.
     *
     * @param FinancialEntityService $service
     * @param int                    $entityId
     */
    public function __construct(FinancialEntityService $service, int $entityId)
    {
        $this->entityId = $entityId;
        $this->service  = $service;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $this->service->generateDocument($this->entityId);
    }
}
