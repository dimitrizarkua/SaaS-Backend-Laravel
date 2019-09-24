<?php

use App\Components\Meetings\Models\Meeting;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Meeting::class, function ($faker) {
    return [
        'title'        => $faker->word,
        'scheduled_at' => $faker->date(),
        'user_id'      => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
    ];
});
