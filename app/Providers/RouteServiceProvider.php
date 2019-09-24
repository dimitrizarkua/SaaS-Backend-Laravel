<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 *
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    protected $routeParamPatterns = [
        'accounting_organization' => '\d+',
        'id'                      => '\d+',
        'user'                    => '\d+',
        'gl_account'              => '\d+',
        'gl_account_id'           => '\d+',
        'role'                    => '\d+',
        'permission'              => '[a-zA-Z\.]+',
        'tag'                     => '\d+',
        'note'                    => '\d+',
        'message'                 => '\d+',
        'document'                => '\d+',
        'country'                 => '\d+',
        'state'                   => '\d+',
        'suburb'                  => '\d+',
        'address'                 => '\d+',
        'category'                => '\d+',
        'status'                  => '\d+',
        'contact'                 => '\d+',
        'meeting'                 => '\d+',
        'location'                => '\d+',
        'team'                    => '\d+',
        'photo'                   => '\d+',
        'service'                 => '\d+',
        'message_template'        => '\d+',
        'job'                     => '\d+',
        'vehicle'                 => '\d+',
        'task'                    => '\d+',
        'run'                     => '\d+',
        'template'                => '\d+',
        'purchase_order'          => '\d+',
        'credit_note'             => '\d+',
        'invoice'                 => '\d+',
        'assessment_report'       => '\d+',
        'section'                 => '\d+',
        'equipment'               => '\d+',
        'material'                => '\d+',
    ];

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->routeParamPatterns as $parameter => $pattern) {
            Route::pattern($parameter, $pattern);
        }

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //Routes for preview emails in the browser
        if (App::environment(['local', 'development'])) {
            Route::middleware('web')
                ->prefix('emails')
                ->namespace($this->namespace)
                ->group(base_path('routes/emails.php'));
        }

        if (!App::environment(['production'])) {
            $router = Route::prefix('v1')
                ->middleware('api')
                ->middleware('auth:api')
                ->namespace($this->namespace);
            $router->post('/management/seed', 'App\Http\Controllers\Management\SeederController@seed');
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('v1')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
