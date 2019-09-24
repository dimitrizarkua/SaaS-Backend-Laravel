<?php

use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Enums\AllowanceTypeChargingIntervals;
use App\Components\UsageAndActuals\Models\AllowanceType;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(AllowanceType::class, function (Faker $faker) {
    return [
        'location_id'              => factory(Location::class)->create()->id,
        'name'                     => $faker->unique()->word,
        'charge_rate_per_interval' => $faker->randomFloat(2, 30, 40),
        'charging_interval'        => $this->faker->randomElement(AllowanceTypeChargingIntervals::values()),
    ];
});
