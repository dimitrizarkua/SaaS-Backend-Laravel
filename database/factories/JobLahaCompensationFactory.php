<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\UsageAndActuals\Models\LahaCompensation;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobLahaCompensation::class, function (Faker $faker) {
    return [
        'job_id'               => factory(Job::class)->create()->id,
        'user_id'              => factory(User::class)->create()->id,
        'creator_id'           => factory(User::class)->create()->id,
        'laha_compensation_id' => factory(LahaCompensation::class)->create()->id,
        'date_started'         => Carbon::now()->format('Y-m-d'),
        'rate_per_day'         => $faker->randomFloat(2, 50, 100),
        'days'                 => $faker->numberBetween(1, 5),
        'approved_at'          => Carbon::now()->addHour(),
        'approver_id'          => factory(User::class)->create()->id,
    ];
});
