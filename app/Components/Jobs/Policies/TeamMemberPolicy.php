<?php

namespace App\Components\Jobs\Policies;

use App\Components\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

/**
 * Class TeamMemberPolicy
 *
 * @package App\Components\Jobs\Policies
 */
class TeamMemberPolicy
{
    use HandlesAuthorization;

    public function isMemberOf(User $user, Team $team): bool
    {
        $result = DB::query()
            ->select(['team_id'])
            ->from('team_user')
            ->where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->first();

        return null !== $result;
    }
}
