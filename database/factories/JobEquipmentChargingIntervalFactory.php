<?php

use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
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
$factory->define(JobEquipmentChargingInterval::class, function (Faker $faker) {
    return [
        'job_equipment_id'                        => function () {
            return factory(JobEquipment::class)->create()->id;
        },
        'equipment_category_charging_interval_id' => function () {
            return factory(EquipmentCategoryChargingInterval::class)->create()->id;
        },
        'charging_interval'                       =>
            $faker->randomElement(EquipmentCategoryChargingIntervals::values()),
        'charging_rate_per_interval'              => $faker->randomFloat(2, 1, 1000),
        'max_count_to_the_next_interval'          => $faker->numberBetween(1, 9),
        'up_to_amount'                            => $faker->randomFloat(2, 1, 1000),
        'up_to_interval_count'                    => $faker->randomNumber(1),
    ];
});
