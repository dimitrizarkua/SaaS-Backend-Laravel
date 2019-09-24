<?php

namespace Tests\Unit\Notifications\Services;

use App\Components\Jobs\Models\JobUser;
use App\Components\Notifications\Models\VO\UserNotificationData;
use Carbon\Carbon;
use Faker\Factory as Faker;

/**
 * Class FakeUserNotificationData
 *
 * @package Tests\Unit\Notifications\Services\UserNotifications
 */
class FakeUserNotificationData
{
    /**
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getStub()
    {
        $faker = Faker::create();

        $jobUser = factory(JobUser::class)->create();

        $data = new UserNotificationData();
        $data->setUserId($jobUser->user_id)
            ->setType($faker->name)
            ->setBody($faker->sentence)
            ->setExpiresAt(Carbon::now()->addMinutes($faker->numberBetween(5, 10)));

        return $data;
    }
}
