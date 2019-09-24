<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Models\User;
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
$factory->define(JobEquipment::class, function (Faker $faker) {
    $intervalsCount = $faker->numberBetween(1, 9);

    return [
        'job_id'                   => function () {
            return factory(Job::class)->create()->id;
        },
        'equipment_id'             => function () {
            return factory(Equipment::class)->create()->id;
        },
        'creator_id'               => function () {
            return factory(User::class)->create()->id;
        },
        'started_at'               => $faker->date('Y-m-d\TH:i:s\Z'),
        'ended_at'                 => $faker->date('Y-m-d\TH:i:s\Z'),
        'interval'                 => $faker->randomElement(EquipmentCategoryChargingIntervals::values()),
        'intervals_count'          => $intervalsCount,
        'intervals_count_override' => $intervalsCount,
        'buy_cost_per_interval'    => $faker->randomFloat(2, 1, 1000),
        'invoice_item_id'          => null,
    ];
});
