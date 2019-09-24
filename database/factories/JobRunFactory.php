<?php

use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRun;
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
$factory->define(JobRun::class, function (Faker $faker) {
    return [
        'location_id' => function () {
            return factory(Location::class)->create()->id;
        },
        'name'        => $faker->word,
        'date'        => $faker->date(),
    ];
});
