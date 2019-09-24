<?php

namespace App\Providers;

use App\Components\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider as BaseServiceProvider;

/**
 * Class PaginationServiceProvider
 *
 * @package App\Providers
 */
class PaginationServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();
        Paginator::setPerPageResolver(function ($perPage = 'per_page', $default = 15) {
            $page = $this->getRequest()->input($perPage);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }

            return $default;
        });
    }

    /**
     * Returns Request instance.
     *
     * @return Request
     */
    private function getRequest(): Request
    {
        return $this->app['request'];
    }
}
