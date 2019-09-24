<?php

namespace App\Components\Notes\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class NoteDetachedFromJob
 *
 * @package App\Components\Notes\Events
 */
class NoteDetachedFromJob
{
    use SerializesModels;

    public $jobId;
    public $noteId;

    /**
     * Create a new event instance.
     *
     * @param int $jobId
     * @param int $noteId
     */
    public function __construct(int $jobId, int $noteId)
    {
        $this->jobId  = $jobId;
        $this->noteId = $noteId;
    }
}
