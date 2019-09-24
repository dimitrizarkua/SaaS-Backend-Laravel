<?php

namespace App\Jobs\Management;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Components\Jobs\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

/**
 * Class SeederJob
 *
 * @package App\Jobs\Management
 */
class SeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $seederClass;

    /**
     * SeederJob constructor.
     *
     * @param string $seederClass
     */
    public function __construct(string $seederClass)
    {
        $this->seederClass = $seederClass;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('db:seed', [
            '--class' => $this->seederClass,
        ]);
    }
}
