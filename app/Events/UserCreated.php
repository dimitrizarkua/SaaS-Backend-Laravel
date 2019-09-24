<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserCreated
 *
 * @package App\Events
 */
class UserCreated
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
