<?php

namespace App\Components\Meetings\Policies;

use App\Components\Meetings\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class MeetingPolicy
 *
 * @package App\Components\Meetings\Policies
 */
class MeetingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the note.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Meetings\Models\Meeting $meeting
     *
     * @return mixed
     */
    public function delete(User $user, Meeting $meeting)
    {
        return $user->id === $meeting->user_id;
    }
}
