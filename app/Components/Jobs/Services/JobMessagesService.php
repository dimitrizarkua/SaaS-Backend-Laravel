<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Events\JobMessageReadOrUnread;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobNotesTemplate;
use App\Components\Jobs\Events\MessageAttachedToJob;
use App\Components\Messages\Events\MessageDetachedFromJob;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\Message;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Core\Utils\Parser;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class JobMessagesService
 *
 * @package App\Components\Jobs\Services
 */
class JobMessagesService extends JobsEntityService implements JobMessagesServiceInterface
{
    /** @var \App\Components\Messages\Interfaces\MessagingServiceInterface */
    private $messagingService;

    /**
     * JobMessagesService constructor.
     *
     * @param \App\Components\Messages\Interfaces\MessagingServiceInterface $messagingService
     */
    public function __construct(MessagingServiceInterface $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function attachMessage(int $jobId, int $messageId, bool $sendImmediately = false): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $message = Message::findOrFail($messageId);

        try {
            DB::transaction(function () use (&$job, $messageId, $sendImmediately) {

                $job->messages()->attach($messageId);
                $job->updateTouchedAt();
            });

            if ($sendImmediately) {
                $this->messagingService->send($messageId);
            }
        } catch (\App\Components\Messages\Exceptions\NotAllowedException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new NotAllowedException('This message is already attached to specified job.');
        }

        $this->dispatchAttachMessageEvents($job, $message);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function hasMessage(int $jobId, int $messageId): bool
    {
        $job = $this->jobsService()->getJob($jobId);

        return $job->messages()->where('id', $messageId)->exists();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \App\Components\Messages\Exceptions\NotAllowedException
     */
    public function sendMessage(int $jobId, int $messageId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        if (!$this->hasMessage($jobId, $messageId)) {
            throw new NotAllowedException('Message is not attached to specified job.');
        }

        $this->messagingService->send($messageId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function readIncomingMessage(int $jobId, int $messageId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->incomingMessages()->updateExistingPivot($messageId, ['read_at' => 'now()']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function readAllIncomingMessages(int $jobId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->incomingMessages()
            ->newPivotStatement()
            ->where('read_at', null)
            ->update(['read_at' => 'now()']);

        event(new JobMessageReadOrUnread());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unreadLatestIncomingMessage(int $jobId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        /** @var Message $lastMessage */
        $lastMessage = $job->incomingMessages()
            ->first();

        if (null !== $lastMessage) {
            $job->incomingMessages()
                ->updateExistingPivot($lastMessage->id, [
                    'read_at' => null,
                ]);

            event(new JobMessageReadOrUnread());
        } else {
            throw new NotAllowedException('Could not make changes because the job has no messages.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function detachMessage(int $jobId, int $messageId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }
        event(new MessageDetachedFromJob($jobId, $messageId));
        $job->messages()->updateExistingPivot($messageId, ['deleted_at' => 'now()']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function composeMessage(int $jobId, int $templateId): string
    {
        $jobData  = $this->jobsService()->getJob($jobId)->toArray();
        $template = JobNotesTemplate::findOrFail($templateId);

        return viewString($template->body, $jobData);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function matchMessageToJob(int $messageId): ?Job
    {
        $message = $this->messagingService->getMessage($messageId);

        // First we try to match by message subject if set
        if ($message->subject) {
            $job = $this->parseJobIdAndFindJob($message->subject);
            if ($job) {
                return $job;
            }

            $job = $this->parseClaimNumberAndFindJob($message->subject);
            if ($job) {
                return $job;
            }
        }

        // And then by message body
        $job = $this->parseJobIdAndFindJob($message->message_body);
        if ($job) {
            return $job;
        }

        $job = $this->parseClaimNumberAndFindJob($message->message_body);
        if ($job) {
            return $job;
        }

        return null;
    }

    /**
     * Convince method that tries to find a job by its id in input string.
     *
     * @param string $input Input string.
     *
     * @return \App\Components\Jobs\Models\Job|null
     */
    private function parseJobIdAndFindJob(string $input): ?Job
    {
        $jobId = Parser::parseJobId($input);
        if (!$jobId) {
            return null;
        }

        return Job::find($jobId);
    }

    /**
     * Convince method that tries to find a job by its claim number in input string.
     *
     * @param string $input Input string.
     *
     * @return \App\Components\Jobs\Models\Job|null
     */
    private function parseClaimNumberAndFindJob(string $input): ?Job
    {
        $claimNumber = Parser::parseClaimNumber($input);
        if (!$claimNumber) {
            return null;
        }

        return Job::query()->where(['claim_number' => $claimNumber])->first();
    }

    /**
     * @param \App\Components\Jobs\Models\Job         $job
     * @param \App\Components\Messages\Models\Message $message
     *
     * @throws \ReflectionException
     */
    private function dispatchAttachMessageEvents(Job $job, Message $message): void
    {
        if (!$job->wasRecentlyCreated) {
            event(new JobUpdated($job, $message->sender_user_id));
        }

        event(new MessageAttachedToJob($job, $message));
        $this->getNotificationService()
            ->dispatchUserMentionedEvent($job, $message, $message->sender_user_id);
    }

    /**
     * @return \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface
     */
    private function getNotificationService(): UserNotificationsServiceInterface
    {
        return app()->make(UserNotificationsServiceInterface::class);
    }
}
