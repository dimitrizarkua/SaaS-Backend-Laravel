<?php

use App\Components\UsageAndActuals\Models\LabourType;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(LabourType::class, function (Faker $faker) {
    return [
        'name'                    => $faker->unique()->word,
        'first_tier_hourly_rate'  => $faker->randomFloat(2, 30, 40),
        'second_tier_hourly_rate' => $faker->randomFloat(2, 50, 80),
        'third_tier_hourly_rate'  => $faker->randomFloat(2, 100, 130),
        'fourth_tier_hourly_rate' => $faker->randomFloat(2, 150, 200),
    ];
});
