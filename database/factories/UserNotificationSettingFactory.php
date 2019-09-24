<?php

use App\Components\Notifications\Models\UserNotificationSetting;
use App\Models\User;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(UserNotificationSetting::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'type'    => $faker->name,
        'value'   => $faker->boolean,
    ];
});
