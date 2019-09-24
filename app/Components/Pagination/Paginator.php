<?php

namespace App\Components\Pagination;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class Paginator
 * Extends of basic paginator.
 *
 * @package App\Components\Pagination
 */
class Paginator extends LengthAwarePaginator
{
    /**
     * The current per_page resolver callback.
     *
     * @var \Closure
     */
    protected static $perPageResolver;

    /**
     * Set the current per_page resolver callback.
     *
     * @param  \Closure $resolver
     *
     * @return void
     */
    public static function setPerPageResolver(Closure $resolver): void
    {
        static::$perPageResolver = $resolver;
    }

    /**
     * Resolve the per_page value from request or return the default value.
     *
     * @param  string $perPage The name of query variable in the URL that contains count of items per page.
     * @param  int    $default Default value of items if resolver callback is not set.
     *
     * @return int
     */
    public static function resolvePerPage(string $perPage = 'per_page', int $default = 15): int
    {
        if (null !== static::$perPageResolver) {
            return \call_user_func(static::$perPageResolver, $perPage, $default);
        }

        return $default;
    }

    /**
     * Convenient method to get only pagination data.
     *
     * @return array
     */
    public function getPaginationData(): array
    {
        return [
            'per_page'     => $this->perPage(),
            'current_page' => $this->currentPage(),
            'last_page'    => $this->lastPage(),
            'total_items'  => $this->total(),
        ];
    }

    /**
     * Convenient method to get only query result data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
