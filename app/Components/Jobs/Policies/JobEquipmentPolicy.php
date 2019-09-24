<?php

namespace App\Components\Jobs\Policies;

use App\Components\Jobs\Models\JobEquipment;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class JobEquipmentPolicy
 *
 * @package App\Components\Jobs\Policies
 */
class JobEquipmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user allows to manage job equipment entries that were created other users.
     *
     * @param \App\Models\User                         $user
     * @param \App\Components\Jobs\Models\JobEquipment $jobEquipment
     *
     * @return boolean
     */
    public function manage(User $user, JobEquipment $jobEquipment): bool
    {
        if ($user->id !== $jobEquipment->creator_id) {
            return app()->make(UsersServiceInterface::class)
                ->hasPermission($user->id, 'jobs.usage.equipment.manage');
        }

        return true;
    }
}
