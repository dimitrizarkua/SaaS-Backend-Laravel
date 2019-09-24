<?php

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\JobStatus;
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
$factory->define(JobStatus::class, function (Faker $faker) {
    return [
        'user_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'status'     => $faker->randomElement(JobStatuses::values()),
        'note'       => $faker->word,
        'created_at' => \Illuminate\Support\Carbon::now(),
    ];
});
