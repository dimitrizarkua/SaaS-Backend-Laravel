<?php

namespace App\Components\Notifications\Interfaces;

use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\UserNotificationData;
use App\Components\Notifications\Models\UserNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Interface UserNotificationsServiceInterface
 *
 * @package App\Components\Notifications\Interfaces
 */
interface UserNotificationsServiceInterface
{
    /**
     * @param int $notificationId
     *
     * @return \App\Components\Notifications\Models\UserNotification
     */
    public function getNotification(int $notificationId): UserNotification;

    /**
     * Creates new notification.
     *
     * @param \App\Components\Notifications\Models\VO\UserNotificationData $notification
     *
     * @return \App\Components\Notifications\Models\UserNotification
     */
    public function createNotification(UserNotificationData $notification): UserNotification;

    /**
     * Marks notification as read.
     *
     * @param int $notificationId
     *
     * @return bool
     */
    public function read(int $notificationId): bool;

    /**
     * Marks all notifications as read.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function readAll(int $userId): bool;

    /**
     * Returns list of all unread notifications for specified user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Support\Collection
     */
    public function listUnreadNotifications(int $userId): Collection;

    /**
     * @param int                   $recipientId User id who should be notified.
     * @param UserNotificationEvent $event       User notification event.
     *
     * @return bool
     */
    public function shouldNotify(int $recipientId, UserNotificationEvent $event): bool;

    /**
     * Dispatches UserMentionedEvent if needed.
     *
     * @param \Illuminate\Database\Eloquent\Model $targetModel
     * @param \Illuminate\Database\Eloquent\Model $contextModel
     * @param int                                 $senderId
     *
     * @throws \ReflectionException
     */
    public function dispatchUserMentionedEvent(Model $targetModel, Model $contextModel, int $senderId = null): void;
}
