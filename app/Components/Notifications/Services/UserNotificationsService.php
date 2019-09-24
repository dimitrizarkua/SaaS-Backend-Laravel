<?php

namespace App\Components\Notifications\Services;

use App\Components\Jobs\Events\JobAssignedToUser;
use App\Components\Jobs\Events\MessageAttachedToJob;
use App\Components\Jobs\Events\NoteAttachedToJob;
use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Services\JobUsersService;
use App\Components\Notifications\Enums\NotificationSettingTypes;
use App\Components\Notifications\Events\UserMentioned;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Exceptions\InvalidAssociationException;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Notifications\Models\UserNotificationSetting;
use App\Components\Notifications\Models\VO\UserNotificationData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionMethod;

/**
 * Class UserNotificationsService
 *
 * @package App\Components\Notifications\Services
 */
class UserNotificationsService implements UserNotificationsServiceInterface
{
    const ASSOCIATION_METHOD_NAME = 'mentionedUsers';

    /**
     * Validates that model is associated with provider model (via ::mentionedUsers() method).
     *
     * @param \Illuminate\Database\Eloquent\Model $model Model (instance) to be validated.
     *
     * @throws \ReflectionException
     */
    private function validateAssociationWithMentionedUsers(Model $model): void
    {
        $exception = new InvalidAssociationException(sprintf(
            'Method ::%s() doesn\'t exist in %s class',
            self::ASSOCIATION_METHOD_NAME,
            get_class($model)
        ));

        if (method_exists($model, self::ASSOCIATION_METHOD_NAME)) {
            $reflection = new ReflectionMethod($model, self::ASSOCIATION_METHOD_NAME);
            if (!$reflection->isPublic()) {
                throw $exception;
            }
        } else {
            throw $exception;
        }
    }

    /**
     * @return \App\Components\Jobs\Services\JobUsersService
     */
    private function getJobUserService(): JobUsersService
    {
        return app()->make(JobUsersServiceInterface::class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getNotification(int $notificationId): UserNotification
    {
        return UserNotification::findOrFail($notificationId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createNotification(UserNotificationData $data): UserNotification
    {
        $userNotification = new UserNotification($data->toArray());
        $userNotification->saveOrFail();

        return $userNotification;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function read(int $userNotificationId): bool
    {
        return $this->getNotification($userNotificationId)
            ->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function readAll(int $userId): bool
    {
        return UserNotification::whereUserId($userId)
            ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function listUnreadNotifications(int $userId): Collection
    {
        return UserNotification::whereUserId($userId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function shouldNotify(int $recipientId, UserNotificationEvent $event): bool
    {
        $specialJobEventTypes = [NoteAttachedToJob::TYPE, MessageAttachedToJob::TYPE, JobAssignedToUser::TYPE];
        $notificationType     = $event->getNotificationType();

        return in_array($notificationType, $specialJobEventTypes, true)
            ? $this->checkSpecialJobCases($recipientId, $event)
            : $this->checkSettingByUser($recipientId, $notificationType);
    }

    /**
     * Checks special cases for jobs.
     *
     * @param int                   $recipientId
     * @param UserNotificationEvent $event
     *
     * @return bool
     */
    private function checkSpecialJobCases(int $recipientId, UserNotificationEvent $event): bool
    {
        $notificationType = $event->getNotificationType();

        if ($notificationType === JobAssignedToUser::TYPE) {
            /** JobAssignedToUser $event */
            $setting = $recipientId === $event->assignedUserId
                ? NotificationSettingTypes::JOB_ASSIGNED_TO_ME
                : NotificationSettingTypes::JOB_ASSIGNED_TO_SOMEONE;

            return $this->checkSettingByUser($recipientId, $setting);
        }

        $jobId = $event->targetModel->id;

        $job = Job::find($jobId);

        $isAssignedJob = $job->assignedUsers()->exists() || $job->assignedTeams()->exists();

        if (empty($isAssignedJob)) {
            $setting = $event->getNotificationType() === NoteAttachedToJob::TYPE
                ? NotificationSettingTypes::NOTE_ADDED_TO_UNASSIGNED_JOB
                : NotificationSettingTypes::MESSAGE_ADDED_TO_UNASSIGNED_JOB;

            return $this->checkSettingByUser($recipientId, $setting);
        }

        $isMyJob = $this->getJobUserService()
            ->isUserAssigned($jobId, $recipientId);

        if ($isMyJob) {
            $setting = $notificationType === NoteAttachedToJob::TYPE
                ? NotificationSettingTypes::NOTE_ADDED_TO_MY_JOB
                : NotificationSettingTypes::MESSAGE_ADDED_TO_MY_JOB;

            return $this->checkSettingByUser($recipientId, $setting);
        }

        $setting = $notificationType === NoteAttachedToJob::TYPE
            ? NotificationSettingTypes::NOTE_ADDED_TO_JOB_OWNED_BY_SOMEONE
            : NotificationSettingTypes::MESSAGE_ADDED_TO_JOB_OWNED_BY_SOMEONE;

        return $this->checkSettingByUser($recipientId, $setting);
    }

    /**
     * Checks user notification settings.
     * There are 3 condition:
     *  1. null - should be notified for specified type (by default null for all users)
     *  2. true - should be notified for specified type
     *  3. false - should not be notified for specified type
     *
     * @param int    $userId
     * @param string $settingType Event type
     *
     * @return bool
     */
    private function checkSettingByUser(int $userId, string $settingType): bool
    {
        $setting = UserNotificationSetting::query()
            ->where([
                'user_id' => $userId,
                'type'    => $settingType,
            ])
            ->first();

        return null === $setting ? true : $setting->value;
    }

    /**
     * Dispatches UserMentionedEvent if needed.
     *
     * @param \Illuminate\Database\Eloquent\Model $targetModel  f.i. job, contact
     * @param \Illuminate\Database\Eloquent\Model $contextModel f.i. note, message
     * @param int                                 $senderId
     *
     * @throws \ReflectionException
     */
    public function dispatchUserMentionedEvent(Model $targetModel, Model $contextModel, int $senderId = null): void
    {
        $this->validateAssociationWithMentionedUsers($contextModel);

        $mentionedUsers = $contextModel->mentionedUsers;

        $mentionedUserIds = null !== $mentionedUsers
            ? $mentionedUsers->pluck('id')->toArray()
            : [];

        if (!empty($mentionedUserIds)) {
            event(new UserMentioned($targetModel, $contextModel, $mentionedUserIds, $senderId));
        }
    }
}
