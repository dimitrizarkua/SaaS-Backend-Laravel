<?php

namespace App\Components\Notifications\Policies;

use App\Components\Notifications\Models\UserNotification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class UserNotificationPolicy
 *
 * @package App\Components\Jobs\Policies
 */
class UserNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User                                      $user
     * @param \App\Components\Notifications\Models\UserNotification $userNotification
     *
     * @return bool
     */
    public function isOwner(User $user, UserNotification $userNotification): bool
    {
        return $userNotification->user_id === $user->id;
    }
}
