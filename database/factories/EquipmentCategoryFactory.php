<?php

use App\Components\UsageAndActuals\Models\EquipmentCategory;
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
$factory->define(EquipmentCategory::class, function (Faker $faker) {
    return [
        'name'                          => $faker->unique()->sentence(2),
        'is_airmover'                   => $faker->boolean,
        'is_dehum'                      => $faker->boolean,
        'default_buy_cost_per_interval' => $faker->randomFloat(2, 1, 1000),
    ];
});
