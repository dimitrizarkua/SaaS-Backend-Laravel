<?php

use App\Components\Notifications\Models\UserNotification;
use App\Models\User;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(UserNotification::class, function (Faker $faker) {
    return [
        'user_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'body'       => $faker->sentence,
        'type'       => $faker->name,
        'expires_at' => \Carbon\Carbon::now()->addMinutes($faker->numberBetween(1, 10)),
    ];
});
