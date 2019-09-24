<?php

namespace App\Components\RBAC;

use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Models\Permission;
use Illuminate\Container\Container;

/**
 * Trait PermissionAwareTrait
 *
 * @package App\Components\RBAC
 */
trait PermissionAwareTrait
{
    /**
     * Returns permission instance.
     *
     * @param string|Permission $permission Permission name or instance.
     *
     * @return \App\Components\RBAC\Models\Permission
     */
    private function getPermissionInstance($permission): Permission
    {
        return $this->getPermissionDataProvider()
            ->getPermissionInstance($permission);
    }

    /**
     * Returns permission data provider implementation instance.
     *
     * @return \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    private function getPermissionDataProvider(): PermissionDataProviderInterface
    {
        return Container::getInstance()->make(PermissionDataProviderInterface::class);
    }
}
