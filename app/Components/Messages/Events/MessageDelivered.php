<?php

namespace App\Components\Messages\Events;

use App\Components\Messages\Models\Message;
use Illuminate\Queue\SerializesModels;

/**
 * Class MessageDelivered
 *
 * @package App\Components\Jobs\Events
 */
class MessageDelivered
{
    use SerializesModels;

    /** @var \App\Components\Messages\Models\Message */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     *
     * @throws \Exception
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
