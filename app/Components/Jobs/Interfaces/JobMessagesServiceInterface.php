<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\Job;

/**
 * Interface JobMessagesServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobMessagesServiceInterface
{
    /**
     * Allows to attach message to a job.
     *
     * @param int  $jobId           Job id.
     * @param int  $messageId       Message id.
     * @param bool $sendImmediately Defines whether message should be sent immediately or not.
     */
    public function attachMessage(int $jobId, int $messageId, bool $sendImmediately = false): void;

    /**
     * Checks whether message is already attached to job or not.
     *
     * @param int $jobId     Job id.
     * @param int $messageId Message id.
     *
     * @return bool
     */
    public function hasMessage(int $jobId, int $messageId): bool;

    /**
     * Convenience method that allows to send message attached to a job.
     *
     * @param int $jobId     Job id.
     * @param int $messageId Message id.
     *
     * @return void
     */
    public function sendMessage(int $jobId, int $messageId): void;

    /**
     * Allows to mark incoming job message as read.
     *
     * @param int $jobId     Job id.
     * @param int $messageId Message id.
     */
    public function readIncomingMessage(int $jobId, int $messageId): void;

    /**
     * Allows to mark all incoming job messages as read.
     *
     * @param int $jobId Job id.
     */
    public function readAllIncomingMessages(int $jobId): void;

    /**
     * Allows to mark the latest incoming job message as unread.
     *
     * @param int $jobId Job id.
     */
    public function unreadLatestIncomingMessage(int $jobId): void;

    /**
     * Allows to detach message from a job (soft-delete message).
     *
     * @param int $jobId     Job id.
     * @param int $messageId Message id.
     */
    public function detachMessage(int $jobId, int $messageId): void;

    /**
     * Allows to compose message from a job using the specified template.
     *
     * @param int $jobId      Job id.
     * @param int $templateId Template id.
     *
     * @return string
     */
    public function composeMessage(int $jobId, int $templateId): string;

    /**
     * Analyzes message and tries to find a correspondent job in the system.
     *
     * @param int $messageId
     *
     * @return \App\Components\Jobs\Models\Job|null
     */
    public function matchMessageToJob(int $messageId): ?Job;
}
