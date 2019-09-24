<?php

namespace App\Components\RBAC\Services;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Interfaces\RoleServiceInterface;
use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Models\PermissionRole;
use App\Components\RBAC\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * Class RolesService
 *
 * @package App\Components\RBAC\Services
 */
class RolesService implements RoleServiceInterface
{
    /**
     * @var PermissionDataProviderInterface
     */
    private $permissionDataProvider;

    /**
     * RolesService constructor.
     *
     * @param PermissionDataProviderInterface $permissionDataProvider
     */
    public function __construct(PermissionDataProviderInterface $permissionDataProvider)
    {
        $this->permissionDataProvider = $permissionDataProvider;
    }

    /**
     * Creates new role.
     *
     * @param string $name        Role name.
     * @param string $description Role description.
     * @param string $displayName Role display name.
     *
     * @return \App\Components\RBAC\Models\Role
     */
    public function createRole(string $name, string $description = null, string $displayName = null): Role
    {
        if (null === $displayName) {
            $displayName = $name;
        }

        return Role::create([
            'name'         => $name,
            'description'  => $description,
            'display_name' => $displayName,
        ]);
    }

    /**
     * Checks whether is role exists.
     *
     * @param string $roleName Role name to check.
     *
     * @return bool
     */
    public function isExists(string $roleName): bool
    {
        $role = Role::whereName($roleName)->first();

        return null !== $role;
    }

    /**
     * Attach many permission to the role for the one call.
     *
     * @param int                   $roleId      Role id.
     * @param string[]|Permission[] $permissions array of permission names or permission instances.
     *
     * @throws
     */
    public function attachPermissions(int $roleId, array $permissions): void
    {
        $dataToInsert = $this->getPermissionInstances($permissions)
            ->map(function (Permission $permission) use ($roleId) {
                return [
                    'role_id'    => $roleId,
                    'permission' => $permission->getName(),
                ];
            })
            ->toArray();

        try {
            PermissionRole::insert($dataToInsert);
        } catch (QueryException $e) {
            $message = 'Unable to attach permissions. Possible reasons: ';
            $message .= 'role does not exists or this role already has one of the given permission.';
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Detach many permissions from the role for the one call.
     *
     * @param int                   $roleId
     * @param string[]|Permission[] $permissions array of permission names or permission instances.
     */
    public function detachPermissions(int $roleId, array $permissions): void
    {
        $dataToDelete = $this->getPermissionInstances($permissions)
            ->map(function (Permission $permission) {
                return [$permission->getName()];
            })
            ->toArray();

        PermissionRole::query()
            ->whereIn('permission', $dataToDelete)
            ->where('role_id', $roleId)
            ->delete();
    }

    /**
     * Checks whether the role has permission.
     *
     * @param int               $roleId     Role id.
     * @param string|Permission $permission Permission name or instance.
     *
     * @return bool
     */
    public function hasPermission(int $roleId, $permission): bool
    {
        $permission = $this->permissionDataProvider
            ->getPermissionInstance($permission);

        $rolePermission = PermissionRole::query()
            ->where('role_id', $roleId)
            ->where('permission', $permission->getName())
            ->first();

        return null !== $rolePermission;
    }

    /**
     * Returns collection of permissions for specific role.
     *
     * @param int $roleId Role id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(int $roleId): Collection
    {
        return Role::findOrFail($roleId)->getPermissions();
    }

    /**
     * Returns list of permission instances.
     *
     * @param string[]|Permission[] $permissions array of permission names or permission instances.
     *
     * @return Permission[]
     */
    private function getPermissionInstances($permissions): Collection
    {
        $instanceList = [];
        foreach ($permissions as $permission) {
            $instanceList[] = $this->permissionDataProvider->getPermissionInstance($permission);
        }

        return Collection::make($instanceList);
    }
}
