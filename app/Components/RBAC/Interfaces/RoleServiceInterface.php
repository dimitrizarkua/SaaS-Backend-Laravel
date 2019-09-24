<?php

namespace App\Components\RBAC\Interfaces;

use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Models\Role;
use Illuminate\Support\Collection;

/**
 * Interface RoleServiceInterface
 *
 * @package App\Components\RBAC\Interfaces
 */
interface RoleServiceInterface
{
    /**
     * Creates new role.
     *
     * @param string $name        Role name.
     * @param string $description Role description.
     * @param string $displayName Role display name.
     *
     * @return \App\Components\RBAC\Models\Role
     */
    public function createRole(string $name, string $description = null, string $displayName = null): Role;

    /**
     * Checks whether is role exists.
     *
     * @param string $roleName Role name to check.
     *
     * @return bool
     */
    public function isExists(string $roleName): bool;

    /**
     * Attach many permission to the role for the one call.
     *
     * @param int                   $roleId      Role id.
     * @param string[]|Permission[] $permissions array of permission names or permission instances.
     */
    public function attachPermissions(int $roleId, array $permissions): void;

    /**
     * Detach many permissions from the role for the one call.
     *
     * @param int                   $roleId
     * @param string[]|Permission[] $permissions array of permission names or permission instances.
     */
    public function detachPermissions(int $roleId, array $permissions): void;

    /**
     * Checks whether the role has permission.
     *
     * @param int               $roleId     Role id.
     * @param string|Permission $permission Permission name or instance.
     *
     * @return bool
     */
    public function hasPermission(int $roleId, $permission): bool;

    /**
     * Returns collection of permissions for specific role.
     *
     * @param int $roleId Role id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(int $roleId): Collection;
}
