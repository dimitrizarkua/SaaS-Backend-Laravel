<?php

namespace App\Providers;

use Adaojunior\Passport\SocialUserResolverInterface;
use App\Components\Pagination\Paginator;
use App\Models\SocialUserResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('Y-m-d\TH:i:s\Z');
        });
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $this->app->bind(LengthAwarePaginator::class, Paginator::class);
        $this->app->singleton(SocialUserResolverInterface::class, SocialUserResolver::class);
    }
}
