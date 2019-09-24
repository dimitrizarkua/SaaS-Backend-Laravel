<?php

namespace App\Components\RBAC;

use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Models\User;

/**
 * Class Authorization
 *
 * @package App\Components\RBAC
 */
class Authorization
{
    use PermissionAwareTrait;

    /**
     * @var \App\Components\RBAC\Interfaces\RBACServiceInterface
     */
    private $service;

    /**
     * PermissionsController constructor.
     *
     * @param RBACServiceInterface $service
     */
    public function __construct(RBACServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Checks whether the user has permission to access a route.
     *
     * @param \App\Models\User $user       Authenticated user.
     * @param  string          $permission Permission name.
     * @param mixed            $arguments  Some arguments passed from controller.
     *
     * @return bool
     */
    public function checkPermission(User $user, $permission, $arguments): bool
    {
        $permissionInstance = $this->getPermissionInstance($permission);

        $hasPermission = $this->service->getUsersService()
            ->hasPermission($user->id, $permission);
        if (false === $hasPermission) {
            return false;
        }

        if (false === $permissionInstance->hasRule()) {
            return true;
        }

        return $permissionInstance->getRule()($user, $arguments);
    }
}
