<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PasswordChanged
 *
 * @package App\Mail
 */
class PasswordChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var User */
    public $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->user->email)
            ->subject('Password has been reset')
            ->view('users.password-changed')
            ->with([
                'url' => Url::getFullUrl(),
            ]);
    }
}
