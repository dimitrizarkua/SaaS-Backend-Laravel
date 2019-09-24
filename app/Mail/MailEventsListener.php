<?php

namespace App\Mail;

use App\Components\Auth\Events\ForgotPasswordRequestedEvent;
use App\Components\Finance\Events\CreditCardPaymentProcessedEvent;
use App\Components\Office365\Events\Office365UserCreated;
use App\Events\PasswordChanged as PasswordChangedEvent;
use App\Events\UserCreated;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Mail;

/**
 * Class MailEventsListener
 *
 * @package App\Mail
 */
class MailEventsListener
{
    /**
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(UserCreated::class, function (UserCreated $event) {
            Mail::queue(new Welcome($event->user));
        });

        $events->listen(Office365UserCreated::class, function (Office365UserCreated $event) {
            Mail::queue(new NewOffice365User($event->user));
        });

        $events->listen(ForgotPasswordRequestedEvent::class, function (ForgotPasswordRequestedEvent $event) {
            Mail::queue(new ForgotPassword($event->user, $event->resetPasswordLink));
        });

        $events->listen(CreditCardPaymentProcessedEvent::class, function (CreditCardPaymentProcessedEvent $event) {
            Mail::queue(new CreditCardPayment($event->recipientEmail, $event->receipt));
        });

        $events->listen(PasswordChangedEvent::class, function (PasswordChangedEvent $event) {
            Mail::queue(new PasswordChanged($event->user));
        });
    }
}
