<?php

namespace App\Components\RBAC\Interfaces;

use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Exceptions\InvalidArgumentException;
use Illuminate\Support\Collection;

/**
 * Interface PermissionDataProviderInterface
 *
 * @package App\Components\RBAC\Interfaces
 */
interface PermissionDataProviderInterface
{
    /**
     * Returns list of all permissions existing in the app.
     *
     * @return Permission[]|Collection
     */
    public function getAllPermissions(): Collection;

    /**
     * Returns permission by its name.
     *
     * @param string|Permission $permission Permission name or instance.
     *
     * @throws InvalidArgumentException if permission doesn't exists.
     * @return Permission
     */
    public function getPermissionInstance($permission): Permission;
}
