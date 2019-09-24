<?php

namespace App\Jobs\Finance;

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class LockFinancialEntity
 *
 * @package App\Jobs\Finance
 */
class LockFinancialEntity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $financialEntities = [
        PurchaseOrder::class,
        Invoice::class,
        CreditNote::class,
    ];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->financialEntities as $entity) {
            call_user_func([$entity, 'shouldBeLocked'])
                ->update([
                    'locked_at' => 'now()',
                ]);
        }
    }
}
