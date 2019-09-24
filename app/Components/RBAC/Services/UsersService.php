<?php

namespace App\Components\RBAC\Services;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\RoleUser;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class UsersService
 *
 * @package App\Components\RBAC\Services
 */
class UsersService implements UsersServiceInterface
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
     * Attach many roles to the user.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function attachRoles(int $userId, $roles): void
    {
        $dataToInsert = $this->getRoleIds($roles)
            ->map(function ($roleId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ];
            })
            ->toArray();

        try {
            RoleUser::insert($dataToInsert);
        } catch (QueryException $e) {
            $message = 'Unable to attach role. Possible reasons: ';
            $message .= 'role does not exists, user does not exists or this user already has one of the given role.';
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Change user role.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function changeRole(int $userId, $roles): void
    {
        $user = User::findOrFail($userId);

        $user->roles()->sync($roles);
    }

    /**
     * Detach many roles from the user by one call.
     *
     * @param int          $userId User id.
     * @param int[]|Role[] $roles  Array of role ids or role instances.
     */
    public function detachRoles(int $userId, $roles): void
    {
        $dataToDelete = $this->getRoleIds($roles)
            ->toArray();

        RoleUser::query()
            ->whereIn('role_id', $dataToDelete)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Checks whether the user has specific role.
     *
     * @param int      $userId User id.
     * @param int|Role $role   Role id or role instance.
     *
     * @return bool
     */
    public function hasRole(int $userId, $role): bool
    {
        $roleId = $this->getRoleId($role);
        $userRole = RoleUser::where('role_id', $roleId)
            ->where('user_id', $userId)
            ->first();

        return null !== $userRole;
    }

    /**
     * Checks whether the user has specific permission.
     *
     * @param int               $userId     User id.
     * @param string|Permission $permission Permission name or instance.
     *
     * @return bool
     */
    public function hasPermission(int $userId, $permission): bool
    {
        $permission = $this->permissionDataProvider->getPermissionInstance($permission);

        $result = Role::query()
            ->leftJoin('permission_role', 'roles.id', '=', 'permission_role.role_id')
            ->leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $userId)
            ->where('permission_role.permission', $permission->getName())
            ->first();

        return $result !== null;
    }

    /**
     * Returns all roles that are attached to the user.
     *
     * @param int $userId User id.
     *
     * @return Collection|Role[]
     */
    public function getRoles(int $userId): Collection
    {
        return Role::whereHas('users', function (Builder $query) use ($userId) {
            $query->where('id', $userId);
        })->get();
    }

    /**
     * Returns list of all permissions attached to the user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection|Permission[]
     */
    public function getPermissions(int $userId): Collection
    {
        return DB::query()
            ->select(DB::raw('DISTINCT(permission_role.permission)'))
            ->from('permission_role')
            ->leftJoin(
                'roles',
                'permission_role.role_id',
                '=',
                'roles.id'
            )
            ->leftJoin(
                'role_user',
                'role_user.role_id',
                '=',
                'roles.id'
            )
            ->where('role_user.user_id', $userId)
            ->get()
            ->map(function ($item) {
                return $this->permissionDataProvider->getPermissionInstance($item->permission);
            });
    }

    /**
     * @inheritdoc
     */
    public function getTeams(int $userId): Collection
    {
        return Team::query()
            ->select([
                'teams.id',
                'teams.name',
            ])
            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
            ->where('team_user.user_id', $userId)
            ->get();
    }

    /**
     * Returns collection of role ids.
     *
     * @param int[]|Role[] $roles Array of Role id or instance.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRoleIds(array $roles): Collection
    {
        $collection = Collection::make();
        foreach ($roles as $role) {
            $collection->push($this->getRoleId($role));
        }

        return $collection;
    }

    /**
     * Returns role id.
     *
     * @param int|Role $role Role id or instance.
     *
     * @return int
     */
    protected function getRoleId($role)
    {
        if ($role instanceof Role) {
            return $role->id;
        }

        return $role;
    }
}
