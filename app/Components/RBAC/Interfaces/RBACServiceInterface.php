<?php

namespace App\Components\RBAC\Interfaces;

/**
 * Interface RBACServiceInterface
 *
 * @package App\Components\RBAC\Interfaces
 */
interface RBACServiceInterface
{
    /**
     * Returns role service instance.
     *
     * @return \App\Components\RBAC\Interfaces\RoleServiceInterface
     */
    public function getRolesService(): RoleServiceInterface;

    /**
     * Returns users service instance.
     *
     * @return \App\Components\RBAC\Interfaces\UsersServiceInterface
     */
    public function getUsersService(): UsersServiceInterface;

    /**
     * Returns permission data provider instance.
     *
     * @return \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    public function getPermissionDataProvider(): PermissionDataProviderInterface;
}
