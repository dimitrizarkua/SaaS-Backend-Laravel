<?php

namespace App\Jobs\Messages;

use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Events\MessageDelivered;
use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use App\Components\Messages\Models\Message;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class DeliverMessage
 *
 * @package App\Jobs\Messages
 */
class DeliverMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Components\Messages\Models\Message
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param \App\Components\Messages\Models\Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return \App\Components\Messages\Models\Message $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Execute the job.
     *
     * @param MessageDeliveryServiceInterface $service Delivery service.
     *
     * @throws \Throwable
     */
    public function handle(MessageDeliveryServiceInterface $service)
    {
        if (!$this->message->canBeForwardedForDeliveryToServiceProvider()) {
            Log::notice(
                sprintf(
                    'Won\'t deliver message [ID:%d] because it\'s status [STATUS:%s] implies no action.',
                    $this->message->id,
                    $this->message->getCurrentStatus()
                ),
                ['message_id' => $this->message->id]
            );

            return;
        }

        $this->message->changeStatus(MessageStatuses::DELIVERY_IN_PROGRESS);
        try {
            $service->deliver($this->message);
            event(new MessageDelivered($this->message));
        } catch (Exception $exception) {
            $this->message->changeStatus(MessageStatuses::DELIVERY_FAILED, $exception->getMessage());
        }
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds(5);
    }
}
