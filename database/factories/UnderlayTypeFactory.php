<?php

use App\Components\AssessmentReports\Models\UnderlayType;
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
$factory->define(UnderlayType::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->sentence(2),
    ];
});
