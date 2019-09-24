<?php

use App\Components\Teams\Models\Team;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Team::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->words(3, true),
    ];
});
