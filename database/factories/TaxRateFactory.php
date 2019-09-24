<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\TaxRate;

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
$factory->define(TaxRate::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'rate' => $faker->randomFloat(2, 0, 1),
    ];
});
