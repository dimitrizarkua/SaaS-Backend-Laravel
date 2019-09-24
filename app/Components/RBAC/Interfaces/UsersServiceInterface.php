<?php

namespace App\Components\RBAC\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\Permission;

/**
 * Interface UsersServiceInterface
 *
 * @package App\Components\RBAC\Interfaces
 */
interface UsersServiceInterface
{
    /**
     * Attach many roles to the user.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function attachRoles(int $userId, $roles): void;

    /**
     * Change user role.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function changeRole(int $userId, $roles): void;

    /**
     * Detach many roles from the user by one call.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function detachRoles(int $userId, $roles): void;

    /**
     * Checks whether the user has specific role.
     *
     * @param int $userId User id.
     * @param     $role
     *
     * @return bool
     */
    public function hasRole(int $userId, $role): bool;

    /**
     * Checks whether the user has specific permission.
     *
     * @param int               $userId     User id.
     * @param string|Permission $permission Permission name or instance.
     *
     * @return bool
     */
    public function hasPermission(int $userId, $permission): bool;

    /**
     * Returns all roles that are attached to the user.
     *
     * @param int $userId User id.
     *
     * @return Collection|Role[]
     */
    public function getRoles(int $userId): Collection;

    /**
     * Returns list of all permissions attached to the user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection|Permission[]
     */
    public function getPermissions(int $userId): Collection;

    /**
     * Returns user teams.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTeams(int $userId): Collection;
}
