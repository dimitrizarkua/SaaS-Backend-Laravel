<?php

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
$factory->define(\App\Components\Messages\Models\DocumentMessage::class, function (Faker $faker) {
    return [
        'document_id' => function () {
            return factory(\App\Components\Documents\Models\Document::class)->create()->id;
        },
        'message_id'  => function () {
            return factory(\App\Components\Messages\Models\Message::class)->create()->id;
        },
    ];
});
