<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PasswordChanged
 *
 * @package App\Events
 */
class PasswordChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \App\Models\User */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
