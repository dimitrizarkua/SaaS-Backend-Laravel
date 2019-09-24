<?php

namespace Tests\API\Notifications;

use App\Components\Notifications\Models\UserNotification;
use Tests\API\ApiTestCase;

/**
 * Class UserNotificationsControllerTest
 *
 * @package Tests\API\Notifications
 * @group   user
 * @group   notification
 * @group   api
 */
class UserNotificationsControllerTest extends ApiTestCase
{
    public function testListUnreadNotifications()
    {
        $count = $this->faker->numberBetween(1, 3);

        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        factory(UserNotification::class, $count)->create([
            'user_id' => $this->user->id,
        ]);

        $url = action('Users\UserNotificationsController@listUnreadNotifications');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');
    }

    public function testReadNotification()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        $jobNotification = factory(UserNotification::class)->create([
            'user_id' => $this->user->id,
        ]);

        $url = action('Users\UserNotificationsController@read', [
            'notification_id' => $jobNotification->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        $jobNotification = UserNotification::find($jobNotification->id);
        self::assertNull($jobNotification);
    }

    public function testReadAllNotifications()
    {
        $count = $this->faker->numberBetween(1, 3);

        /** @var \Illuminate\Database\Eloquent\Collection $notifications */
        factory(UserNotification::class, $count)->create([
            'user_id' => $this->user->id,
        ]);

        $url = action('Users\UserNotificationsController@readAll');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        $unreadNotifications = UserNotification::query()
            ->where('user_id', $this->user->id)
            ->get();

        self::assertEmpty($unreadNotifications);
    }
}
