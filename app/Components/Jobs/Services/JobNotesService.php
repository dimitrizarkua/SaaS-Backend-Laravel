<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Events\NoteAttachedToJob;
use App\Components\Jobs\Models\Job;
use App\Components\Notes\Events\NoteDetachedFromJob;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class JobNotesService
 *
 * @package App\Components\Jobs\Services
 */
class JobNotesService extends JobsEntityService implements JobNotesServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function addNote(int $jobId, int $noteId, string $status = null, bool $mergeable = true): void
    {
        $job = $this->jobsService()->getJob($jobId);

        if ($job->isClosed() && $mergeable) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $note = Note::findOrFail($noteId);
        try {
            DB::transaction(function () use (&$job, $note, $status, $mergeable) {
                if (null !== $status) {
                    $status = $job->changeStatus($status, null, $note->user_id);
                    $data   = ['job_status_id' => $status->id];
                }
                $data['mergeable'] = $mergeable;

                $job->notes()->attach($note->id, $data);
                $job->updateTouchedAt();
            });
        } catch (NotAllowedException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new NotAllowedException('This note is already added to specified job.');
        }

        $this->dispatchAddNoteEvents($job, $note);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeNote(int $jobId, int $noteId): void
    {
        $job = $this->jobsService()->getJob($jobId);

        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        event(new NoteDetachedFromJob($jobId, $noteId));
        $job->notes()->updateExistingPivot($noteId, ['deleted_at' => 'now()']);
    }

    /**
     * @param \App\Components\Jobs\Models\Job   $job
     * @param \App\Components\Notes\Models\Note $note
     *
     * @throws \ReflectionException
     */
    private function dispatchAddNoteEvents(Job $job, Note $note): void
    {
        event(new NoteAttachedToJob($job, $note));
        event(new JobUpdated($job, $note->user_id));

        $this->getNotificationService()
            ->dispatchUserMentionedEvent($job, $note, $note->user_id);
    }

    /**
     * @return \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface
     */
    private function getNotificationService(): UserNotificationsServiceInterface
    {
        return app()->make(UserNotificationsServiceInterface::class);
    }
}
