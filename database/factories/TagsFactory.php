<?php

use App\Components\Tags\Enums\TagTypes;
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
$factory->define(\App\Components\Tags\Models\Tag::class, function (Faker $faker) {
    return [
        'type'     => $faker->randomElement(TagTypes::values()),
        'name'     => $faker->unique()->sentence(2),
        'is_alert' => $faker->boolean,
    ];
});
