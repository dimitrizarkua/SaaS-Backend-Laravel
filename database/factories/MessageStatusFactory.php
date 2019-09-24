<?php

use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageStatus;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(MessageStatus::class, function (Faker $faker) {
    return [
        'message_id' => function () {
            return factory(Message::class)->create()->id;
        },
        'status'     => $faker->randomElement(MessageStatuses::values()),
    ];
});
