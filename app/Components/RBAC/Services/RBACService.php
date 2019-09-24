<?php

namespace App\Components\RBAC\Services;

use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\RBAC\Interfaces\RoleServiceInterface;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;

/**
 * Class RBACService
 *
 * @package App\Components\RBAC\Services
 */
class RBACService implements RBACServiceInterface
{
    /**
     * @var RoleServiceInterface
     */
    private $rolesService;
    /**
     * @var UsersServiceInterface;
     */
    private $usersService;

    /**
     * @var PermissionDataProviderInterface
     */
    private $permissionDataProvider;

    /**
     * RBACService constructor.
     *
     * @param RoleServiceInterface            $rolesService
     * @param UsersServiceInterface           $usersService
     * @param PermissionDataProviderInterface $permissionDataProvider
     */
    public function __construct(
        RoleServiceInterface $rolesService,
        UsersServiceInterface $usersService,
        PermissionDataProviderInterface $permissionDataProvider
    ) {
        $this->rolesService           = $rolesService;
        $this->usersService           = $usersService;
        $this->permissionDataProvider = $permissionDataProvider;
    }


    /**
     * Returns role service instance.
     *
     * @return RoleServiceInterface
     */
    public function getRolesService(): RoleServiceInterface
    {
        return $this->rolesService;
    }

    /**
     * Returns users service instance.
     *
     * @return UsersServiceInterface
     */
    public function getUsersService(): UsersServiceInterface
    {
        return $this->usersService;
    }

    /**
     * Returns permission data provider instance.
     *
     * @return \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    public function getPermissionDataProvider(): PermissionDataProviderInterface
    {
        return $this->permissionDataProvider;
    }
}
