<?php

use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLabour;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobLabour::class, function (Faker $faker) {
    return [
        'job_id'                  => factory(Job::class)->create()->id,
        'labour_type_id'          => factory(LabourType::class)->create()->id,
        'worker_id'               => factory(User::class)->create()->id,
        'creator_id'              => factory(User::class)->create()->id,
        'started_at'              => Carbon::now()->subHour(),
        'ended_at'                => Carbon::now()->addMinutes($faker->numberBetween(60, 360)),
        'started_at_override'     => Carbon::now()->subHour(),
        'ended_at_override'       => Carbon::now()->addMinutes($faker->numberBetween(60, 360)),
        'break'                   => $faker->numberBetween(0, 120),
        'first_tier_hourly_rate'  => $faker->randomFloat(2, 30, 40),
        'second_tier_hourly_rate' => $faker->randomFloat(2, 50, 80),
        'third_tier_hourly_rate'  => $faker->randomFloat(2, 100, 130),
        'fourth_tier_hourly_rate' => $faker->randomFloat(2, 150, 200),
        'calculated_total_amount' => $faker->randomFloat(2, 100, 100),
        'first_tier_time_amount'  => $faker->numberBetween(0, 120),
        'second_tier_time_amount' => $faker->numberBetween(0, 120),
        'third_tier_time_amount'  => $faker->numberBetween(0, 120),
        'fourth_tier_time_amount' => $faker->numberBetween(0, 120),
        'invoice_item_id'         => factory(InvoiceItem::class)->create()->id,
    ];
});
