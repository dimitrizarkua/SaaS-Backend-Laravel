<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class Welcome
 *
 * @package App\Mail
 */
class Welcome extends Mailable implements ShouldQueue
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
            ->subject('Welcome to Steamatic NIS')
            ->view('users.created')
            ->with([
                'url' => Url::getFullUrl(),
            ]);
    }
}
