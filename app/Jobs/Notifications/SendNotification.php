<?php

namespace App\Jobs\Notifications;

use App\Components\Jobs\Events\JobUpdated;
use App\Components\Notifications\Events\UserNotificationCreated;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Notifications\Models\VO\UserNotificationData;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class SendNotification
 *
 * @package App\Jobs\Notifications
 */
class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Components\Notifications\Events\UserNotificationEvent
     */
    protected $event;

    /**
     * SendNotification constructor.
     *
     * @param \App\Components\Notifications\Events\UserNotificationEvent
     */
    public function __construct(UserNotificationEvent $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @param \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface $notificationsService
     *
     * @return void
     *
     * @throws \JsonMapper_Exception
     */
    public function handle(UserNotificationsServiceInterface $notificationsService)
    {
        $eventType = $this->event->getNotificationType();

        if ($this->shouldNotifyFollowersOnly($eventType)) {
            $this->notifyFollowers($notificationsService);

            return;
        }

        $this->getRecipientsQuery()
            ->chunk(100, function ($recipients) use ($notificationsService) {
                $targetModel = $this->event->targetModel;

                foreach ($recipients as $recipient) {
                    $shouldNotify = $notificationsService->shouldNotify($recipient->id, $this->event);

                    if (empty($shouldNotify)) {
                        continue;
                    }

                    $notificationData = $this->prepareNotificationData($recipient);

                    if (empty($notificationData->getBody())) {
                        continue;
                    }

                    $notification = $notificationsService->createNotification($notificationData);
                    $targetModel->notifications()->attach($notification->id);

                    event(new UserNotificationCreated($notification));
                }
            });
    }

    /**
     * @param string $notificationType
     *
     * @return bool
     */
    private function shouldNotifyFollowersOnly(string $notificationType)
    {
        return $notificationType === JobUpdated::TYPE;
    }

    /**
     * Returns query with users who don't have settings (means on by default) and setiings with value = true.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getRecipientsQuery()
    {
        return User::query()
            ->select('id');
    }

    /**
     * @param \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface $userNotificationsService
     *
     * @throws \JsonMapper_Exception
     */
    private function notifyFollowers(UserNotificationsServiceInterface $userNotificationsService): void
    {
        $targetModel = $this->event->targetModel;

        foreach ($targetModel->followers as $follower) {
            $notificationData = $this->prepareNotificationData($follower);

            if (empty($notificationData->getBody())) {
                continue;
            }

            $notification = $userNotificationsService->createNotification($notificationData);

            $targetModel->notifications()->attach($notification->id);

            event(new UserNotificationCreated($notification));
        }
    }

    /**
     * Returns UserNotificationData object with filled user id, event type and body.
     *
     * @param \App\Models\User $recipient
     *
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     * @throws \JsonMapper_Exception
     */
    private function prepareNotificationData(User $recipient): UserNotificationData
    {
        return (new UserNotificationData())
            ->setUserId($recipient->id)
            ->setType($this->event->getNotificationType())
            ->setBody($this->event->getBodyText($recipient));
    }
}
