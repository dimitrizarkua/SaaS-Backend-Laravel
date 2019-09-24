<?php

use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Components\UsageAndActuals\Models\EquipmentCategoryInsurerContract;
use App\Components\UsageAndActuals\Models\InsurerContract;
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
$factory->define(EquipmentCategoryInsurerContract::class, function (Faker $faker) {
    return [
        'insurer_contract_id'                     => function () {
            return factory(InsurerContract::class)->create()->id;
        },
        'equipment_category_charging_interval_id' => function () {
            return factory(EquipmentCategoryChargingInterval::class)->create()->id;
        },
        'name'                                    => $faker->words(3, true),
        'up_to_amount'                            => $faker->randomFloat(2, 1, 1000),
        'up_to_interval_count'                    => $faker->randomNumber(1),
    ];
});
