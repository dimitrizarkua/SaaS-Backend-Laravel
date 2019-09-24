<?php

namespace App\Jobs\Messages;

use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class HandleExternalMessageStatusUpdate
 *
 * @package App\Jobs\Messages
 */
class HandleExternalMessageStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $messageType;

    /**
     * @var array
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param string $messageType Message type.
     * @param array  $data        Message status update data from provider.
     *
     * @see \App\Components\Messages\Enums\MessageTypes
     */
    public function __construct(string $messageType, array $data)
    {
        $this->messageType = $messageType;
        $this->data        = $data;
    }

    /**
     * Execute the job.
     *
     * @param MessageDeliveryServiceInterface $service Delivery service.
     *
     * @throws
     */
    public function handle(MessageDeliveryServiceInterface $service)
    {
        $service->handleMessageStatusUpdateFromProvider($this->messageType, $this->data);
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
