<?php

namespace App\Console\Commands;

use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\RBAC\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

/**
 * Class RBACCommand
 *
 * @package App\Console\Commands
 */
class RBACCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:grant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Admin role with whole list of permission and optionally assign the role to user';

    /**
     * @var \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    protected $permissionDataProvider;

    /**
     * @var RBACServiceInterface
     */
    protected $service;

    /**
     * RBACCommand constructor.
     *
     * @param \App\Components\RBAC\Interfaces\RBACServiceInterface $service
     */
    public function __construct(RBACServiceInterface $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $roleName = 'Admin';

        $isRoleExists = $this->service->getRolesService()->isExists($roleName);
        if ($isRoleExists) {
            $role = Role::whereName($roleName)->first();
        } else {
            $role = $this->service->getRolesService()->createRole($roleName);
        }

        $permissions = $this->service->getPermissionDataProvider()->getAllPermissions();
        $this->service->getRolesService()->detachPermissions($role->id, $permissions->all());
        $this->service->getRolesService()->attachPermissions($role->id, $permissions->all());

        $this->info('Role \'Admin\' was successfully created');

        if (true === $this->confirm('Do you want to assign the role to specific user?')) {
            $userEmail = $this->ask('User email:');
            $user      = User::whereEmail($userEmail)->firstOrFail();
            $this->service->getUsersService()->attachRoles($user->id, [$role]);
            $this->info('Success');

            return;
        }

        if (false === $this->confirm('Do you want to assign the role to all users?')) {
            return;
        }

        if (App::environment(['production'])) {
            $this->error('This operation is not allowed for production environment');
        }

        $users = User::all();
        foreach ($users as $user) {
            try {
                $this->service->getUsersService()->attachRoles($user->id, [$role]);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
