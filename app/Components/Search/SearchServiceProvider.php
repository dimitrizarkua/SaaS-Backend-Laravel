<?php

namespace App\Components\Search;

use App\Components\Locations\Events\UsersAttachedEvent;
use App\Components\Locations\Events\UsersDetachedEvent;
use App\Components\Locations\Models\Location;
use App\Components\Search\Observers\LocationObserver;
use App\Components\Teams\Models\Team;
use App\Components\Search\Observers\TeamObserver;
use App\Components\Search\Observers\UserObserver;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class SearchServiceProvider
 *
 * @package App\Components\Search
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (App::environment(['testing'])) {
            return;
        }

        User::observe(UserObserver::class);
        Team::observe(TeamObserver::class);
        Location::observe(LocationObserver::class);
        Event::listen(UsersAttachedEvent::class, [LocationObserver::class, 'usersAttached']);
        Event::listen(UsersDetachedEvent::class, [LocationObserver::class, 'usersDetached']);
    }
}
