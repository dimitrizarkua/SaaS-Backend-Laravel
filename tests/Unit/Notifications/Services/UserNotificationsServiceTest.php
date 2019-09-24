<?php

namespace Tests\Unit\Notifications\Services;

use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobUserNotification;
use App\Components\Notifications\Models\UserNotification;
use App\Models\User;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class UserNotificationsServiceTest
 *
 * @package  Tests\Unit\Notifications\Services
 * @group   jobs
 * @group   services
 * @group   notifications
 */
class UserNotificationsServiceTest extends TestCase
{
    /** @var \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface */
    private $service;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(UserNotificationsServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateNotification()
    {
        $stub = FakeUserNotificationData::getStub();

        $created = $this->service->createNotification($stub);

        $notification = UserNotification::findOrFail($created->id);
        self::assertEquals($stub->getBody(), $notification->body);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobNotification()
    {
        $stub = FakeUserNotificationData::getStub();

        $created = $this->service->createNotification($stub);

        /** @var \App\Components\Jobs\Models\Job $job */
        $job = factory(Job::class)->create();
        $job->notifications()->attach($created->id);

        $notification = UserNotification::findOrFail($created->id);

        $jobUserNotification = JobUserNotification::query()
            ->where([
                'job_id'               => $job->id,
                'user_notification_id' => $notification->id,
            ])->get();

        self::assertNotEmpty($jobUserNotification);
        self::assertEquals($stub->getBody(), $notification->body);
    }

    public function testReadNotification()
    {
        $count = $this->faker->numberBetween(1, 3);

        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        $notifications = factory(UserNotification::class, $count)
            ->create();

        $readNotificationId = $notifications->first()->id;

        $this->service->read($readNotificationId);

        /** @var UserNotification $readNotification */
        $unreadNotifications = UserNotification::find($readNotificationId);

        self::assertEmpty($unreadNotifications);
    }

    public function testReadAllNotifications()
    {
        $count = $this->faker->numberBetween(1, 3);

        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        $notifications = factory(UserNotification::class, $count)
            ->create();

        $userId = $notifications->first()->user_id;

        $this->service->readAll($userId);

        /** @var UserNotification $readNotification */
        $unReadNotifications = UserNotification::query()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        self::assertEmpty($unReadNotifications);
    }

    public function testListUnreadNotifications()
    {
        $count = $this->faker->numberBetween(1, 3);

        $user = factory(User::class)->create();

        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        factory(UserNotification::class, $count)->create([
            'user_id' => $user->id,
        ]);

        $unReadNotifications = $this->service->listUnreadNotifications($user->id);

        self::assertEquals($count, count($unReadNotifications));
        self::assertNull($unReadNotifications->first()->deleted_at);
    }
}
