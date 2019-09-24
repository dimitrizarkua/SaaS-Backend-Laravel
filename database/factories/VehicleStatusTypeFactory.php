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
$factory->define(\App\Components\Operations\Models\VehicleStatusType::class, function (Faker $faker) {
    return [
        'name'                      => $faker->word,
        'makes_vehicle_unavailable' => $faker->boolean,
        'is_default'                => $faker->boolean,
    ];
});
