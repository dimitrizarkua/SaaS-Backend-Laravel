<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class BroadcastServiceProvider
 *
 * @package App\Providers
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!\App::environment('testing') && 'null' !== config('broadcasting.default')) {
            require base_path('routes/channels.php');
        }
    }
}
