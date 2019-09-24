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
$factory->define(\App\Components\Addresses\Models\State::class, function (Faker $faker) {
    return [
        'country_id' => function () {
            return factory(\App\Components\Addresses\Models\Country::class)->create()->id;
        },
        'name'       => $faker->unique()->word,
        'code'       => $faker->word,
    ];
});
