<?php

use App\Components\UsageAndActuals\Models\MeasureUnit;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(MeasureUnit::class, function (Faker $faker) {
    return [
        'name' => $faker->title,
        'code' => $faker->postcode,
    ];
});
