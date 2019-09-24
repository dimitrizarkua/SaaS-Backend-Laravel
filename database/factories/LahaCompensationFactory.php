<?php

use App\Components\UsageAndActuals\Models\LahaCompensation;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(LahaCompensation::class, function (Faker $faker) {
    return [
        'rate_per_day' => $faker->randomFloat(2, 30, 40),
    ];
});
