<?php

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
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
$factory->define(EquipmentCategoryChargingInterval::class, function (Faker $faker) {
    return [
        'equipment_category_id'          => function () {
            return factory(EquipmentCategory::class)->create()->id;
        },
        'charging_interval'              => $faker->randomElement(EquipmentCategoryChargingIntervals::values()),
        'charging_rate_per_interval'     => $faker->randomFloat(2, 1, 1000),
        'max_count_to_the_next_interval' => $faker->numberBetween(1, 9),
        'is_default'                     => true,
    ];
});
