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
$factory->define(\App\Components\Jobs\Models\JobTaskType::class, function (Faker $faker) {
    return [
        'name'                     => $faker->unique()->word,
        'can_be_scheduled'         => $faker->boolean,
        'default_duration_minutes' => $faker->numberBetween(30, 1440),
        'kpi_hours'                => $faker->numberBetween(0, 24),
        'kpi_include_afterhours'   => $faker->boolean,
        'color'                    => $faker->randomNumber(),
        'deleted_at'               => null,
    ];
});
