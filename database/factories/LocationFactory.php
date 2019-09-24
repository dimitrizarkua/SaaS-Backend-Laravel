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
$factory->define(\App\Components\Locations\Models\Location::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->regexify('[A-Z]{2,5}'),
        'name' => $faker->unique()->city,
    ];
});
