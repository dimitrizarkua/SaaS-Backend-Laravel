<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ForgotPassword
 *
 * @package App\Mail
 */
class ForgotPassword extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var string */
    public $resetPasswordLink;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     *
     * @return void
     */
    public function __construct(User $user, string $resetPasswordLink)
    {
        $this->user              = $user;
        $this->resetPasswordLink = $resetPasswordLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->user->email)
            ->subject('Reset your password')
            ->view('users.forgot-password');
    }
}
