<?php

use App\Components\UsageAndActuals\Models\Material;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Material::class, function (Faker $faker) {
    return [
        'name'                       => $faker->title,
        'measure_unit_id'            => function () {
            return factory(MeasureUnit::class)->create()->id;
        },
        'default_sell_cost_per_unit' => $faker->randomFloat(2, 100, 200),
        'default_buy_cost_per_unit'  => $faker->randomFloat(2, 50, 100),
    ];
});
