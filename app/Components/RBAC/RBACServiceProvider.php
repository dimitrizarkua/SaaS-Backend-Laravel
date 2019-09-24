<?php

namespace App\Components\RBAC;

use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\RBAC\Interfaces\RoleServiceInterface;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Components\RBAC\Services\RBACService;
use App\Components\RBAC\Services\RolesService;
use App\Components\RBAC\Services\UsersService;
use Illuminate\Support\ServiceProvider;

/**
 * Class RBACServiceProvider
 *
 * @package App\Components\RBAC
 */
class RBACServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        RBACServiceInterface::class            => RBACService::class,
        RoleServiceInterface::class            => RolesService::class,
        UsersServiceInterface::class           => UsersService::class,
        PermissionDataProviderInterface::class => ConfigBasedPermissionDataProvider::class,
    ];
}
