<?php

namespace App\Components\Messages\Policies;

use App\Components\Messages\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class MessagePolicy
 *
 * @package App\Components\Messages\Policies
 */
class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can attach a message to some entity.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Messages\Models\Message $message
     *
     * @return mixed
     */
    public function send(User $user, Message $message)
    {
        return $user->id === $message->sender_user_id;
    }

    /**
     * Determine whether the user can attach a message to some entity.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Messages\Models\Message $message
     *
     * @return mixed
     */
    public function attach(User $user, Message $message)
    {
        return $user->id === $message->sender_user_id;
    }

    /**
     * Determine whether the user can detach a message from some entity.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Messages\Models\Message $message
     *
     * @return mixed
     */
    public function detach(User $user, Message $message)
    {
        return $user->id === $message->sender_user_id;
    }

    /**
     * Determine whether the user can update the message.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Messages\Models\Message $message
     *
     * @return mixed
     */
    public function update(User $user, Message $message)
    {
        return $user->id === $message->sender_user_id;
    }

    /**
     * Determine whether the user can delete the message.
     *
     * @param  \App\Models\User                        $user
     * @param  \App\Components\Messages\Models\Message $message
     *
     * @return mixed
     */
    public function delete(User $user, Message $message)
    {
        return $user->id === $message->sender_user_id;
    }
}
