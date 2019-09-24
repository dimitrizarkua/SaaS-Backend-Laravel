<?php

namespace App\Components\Messages\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class MessageDetachedFromJob
 *
 * @package App\Components\Messages\Events
 */
class MessageDetachedFromJob
{
    use SerializesModels;

    public $jobId;
    public $messageId;

    /**
     * Create a new event instance.
     *
     * @param int $jobId
     * @param int $messageId
     */
    public function __construct(int $jobId, int $messageId)
    {
        $this->jobId     = $jobId;
        $this->messageId = $messageId;
    }
}
