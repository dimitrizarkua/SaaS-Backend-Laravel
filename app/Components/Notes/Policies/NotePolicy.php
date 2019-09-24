<?php

namespace App\Components\Notes\Policies;

use App\Components\Notes\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class NotePolicy
 *
 * @package App\Components\Notes\Policies
 */
class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can attach a note to some entity.
     *
     * @param  \App\Models\User                  $user
     * @param  \App\Components\Notes\Models\Note $note
     *
     * @return mixed
     */
    public function attach(User $user, Note $note)
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can detach a note from some entity.
     *
     * @param  \App\Models\User                  $user
     * @param  \App\Components\Notes\Models\Note $note
     *
     * @return mixed
     */
    public function detach(User $user, Note $note)
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can update the note.
     * This rule also applied to notes documents modifications.
     *
     * @param  \App\Models\User                  $user
     * @param  \App\Components\Notes\Models\Note $note
     *
     * @return mixed
     */
    public function update(User $user, Note $note)
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can delete the note.
     *
     * @param  \App\Models\User                  $user
     * @param  \App\Components\Notes\Models\Note $note
     *
     * @return mixed
     */
    public function delete(User $user, Note $note)
    {
        return $user->id === $note->user_id;
    }
}
