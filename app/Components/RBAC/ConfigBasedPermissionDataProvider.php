<?php

namespace App\Components\RBAC;

use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Exceptions\InvalidConfigException;
use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use Illuminate\Support\Collection;

/**
 * Class ConfigBasedPermissionDataProvider
 *
 * @package App\Components\RBAC
 */
class ConfigBasedPermissionDataProvider implements PermissionDataProviderInterface
{
    /**
     * List of existing permissions.
     *
     * @var Permission[]
     */
    private $permissions;

    /**
     * Returns list of all permissions existing in the app.
     *
     * @return Permission[]
     */
    public function getAllPermissions(): Collection
    {
        if (null === $this->permissions) {
            $this->permissions = $this->readFromConfig();
        }

        return Collection::make($this->permissions);
    }

    /**
     * Returns permission by its name.
     *
     * @param string|Permission $permission Permission name or instance.
     *
     * @throws InvalidArgumentException if permission doesn't exists.
     * @return Permission
     */
    public function getPermissionInstance($permission): Permission
    {
        if ($permission instanceof Permission) {
            $permissionName = $permission->getName();
        } else {
            $permissionName = $permission;
        }

        if (false === $this->isSet($permissionName)) {
            throw new InvalidArgumentException(sprintf('Permission %s doesn\'t exists', $permissionName));
        }

        return $this->getAllPermissions()
            ->get($permissionName);
    }

    /**
     * Checks whether is permission exists.
     *
     * @param string $permissionName Name of permission to be checked.
     *
     * @return bool
     */
    public function isSet(string $permissionName): bool
    {
        return $this->getAllPermissions()
            ->has($permissionName);
    }

    /**
     * Reads permission list from config file (config/rbac.php).
     *
     * @return Permission[]
     */
    private function readFromConfig(): array
    {
        $instanceList = [];
        $permissions  = config('rbac.permissions', []);

        foreach ($permissions as $permission) {
            if (false === isset($permission['name'])) {
                throw new InvalidConfigException('\'name\' is required key for permission config');
            }

            $name        = $permission['name'];
            $displayName = $permission['displayName'] ?? null;
            $description = $permission['description'] ?? null;
            $rule        = $permission['rule'] ?? null;

            $instanceList[$name] = new Permission($name, $displayName, $description, $rule);
        }

        return $instanceList;
    }
}
