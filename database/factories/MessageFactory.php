<?php

use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Models\Message;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Message::class, function (Faker $faker) {
    return [
        'sender_user_id'             => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'message_type'               => $faker->randomElement(MessageTypes::values()),
        'from_address'               => $faker->email,
        'from_name'                  => $faker->name,
        'subject'                    => $faker->sentence,
        'message_body'               => $faker->text,
        'external_system_message_id' => $faker->uuid,
    ];
});
