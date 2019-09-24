<?php

namespace App\Components\Notifications\Events;

use App\Components\Notifications\Models\UserNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserNotificationCreated
 *
 * @package App\Components\Jobs\Events
 */
class UserNotificationCreated implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue = 'broadcasting';

    /** @var UserNotification */
    public $userNotification;

    /**
     * UserNotificationCreated constructor.
     *
     * @param UserNotification $userNotification
     */
    public function __construct(UserNotification $userNotification)
    {
        $this->userNotification = $userNotification;
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return $this->userNotification->type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel(
            sprintf('user-%d', $this->userNotification->user_id)
        );
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {
        $notificationBodyJson = $this->userNotification->body;

        $bodyData = json_decode($notificationBodyJson, true);

        $response = [
            'notification' => [
                'id'   => $this->userNotification->id,
                'type' => $this->userNotification->type,
                'body' => $notificationBodyJson,
            ],
            'target'       => $bodyData['target'],
        ];

        if (!isset($bodyData['context'])) {
            return $response;
        }

        $response['context'] = $bodyData['context'];

        return $response;
    }
}
