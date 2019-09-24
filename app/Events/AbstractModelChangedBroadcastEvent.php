<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class AbstractModelChangedBroadcastEvent
 *
 * @package App\Events
 */
abstract class AbstractModelChangedBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue = 'broadcasting';

    /** @var int */
    public $targetId;

    /** @var int */
    public $senderId;

    /** @var array */
    public $updatedFields;

    /** @var string */
    public $targetType;

    /**
     * Create a new event instance.
     *
     * @param int   $id
     * @param int   $senderId
     * @param array $updatedFields
     */
    public function __construct(int $id, int $senderId, array $updatedFields)
    {
        $this->targetId      = $id;
        $this->senderId      = $senderId;
        $this->updatedFields = $updatedFields;
        $this->targetType    = snake_case(class_basename($this->getModelClass()));
    }

    /**
     * Get the name the event should be called.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return $this->targetType . '.' . $this->getEventType();
    }

    /**
     * Get the model class.
     *
     * @return string
     */
    abstract public function getModelClass();

    /**
     * Get the event type.
     *
     * @return string
     */
    abstract public function getEventType();

    /**
     * Get the channel name.
     *
     * @return string
     */
    abstract public function getChannelName();

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->getChannelName());
    }

    /**
     * Get the data that should be sent with the broadcasted event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'notification' => [
                'type'   => $this->broadcastAs(),
                'sender' => [
                    'id' => $this->senderId,
                ],
            ],
            'target'       => [
                'id'             => $this->targetId,
                'type'           => $this->targetType,
                'updated_fields' => $this->updatedFields,
            ],
        ];
    }
}
