<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\UsageAndActuals\Models\AllowanceType;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobAllowance::class, function (Faker $faker) {
    return [
        'job_id'                   => factory(Job::class)->create()->id,
        'user_id'                  => factory(User::class)->create()->id,
        'creator_id'               => factory(User::class)->create()->id,
        'allowance_type_id'        => factory(AllowanceType::class)->create()->id,
        'date_given'               => Carbon::now()->format('Y-m-d'),
        'charge_rate_per_interval' => $faker->randomFloat(2, 50, 100),
        'amount'                   => $faker->numberBetween(1, 5),
        'approved_at'              => Carbon::now()->addHour(),
        'approver_id'              => factory(User::class)->create()->id,
    ];
});
