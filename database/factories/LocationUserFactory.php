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
$factory->define(\App\Components\Locations\Models\LocationUser::class, function (Faker $faker) {
    return [
        'location_id' => function () {
            return factory(\App\Components\Locations\Models\Location::class)->create()->id;
        },
        'user_id'     => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'primary'     => $faker->boolean,
    ];
});
