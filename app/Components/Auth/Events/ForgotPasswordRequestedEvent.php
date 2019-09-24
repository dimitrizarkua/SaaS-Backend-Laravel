<?php

namespace App\Components\Auth\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ForgotPasswordRequestedEvent
 *
 * @package App\Components\Auth\Events
 */
class ForgotPasswordRequestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    public $resetPasswordLink;
    /**
     * @var User
     */
    public $user;

    /**
     * ForgotPasswordRequestedEvent constructor.
     *
     * @param User   $user
     * @param string $resetPasswordLink
     */
    public function __construct(User $user, string $resetPasswordLink)
    {
        $this->user              = $user;
        $this->resetPasswordLink = $resetPasswordLink;
    }
}
