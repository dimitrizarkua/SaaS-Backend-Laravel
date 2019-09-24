<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobRoom;
use App\Components\AssessmentReports\Models\FlooringType;
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
$factory->define(JobRoom::class, function (Faker $faker) {
    return [
        'job_id'             => function () {
            return factory(Job::class)->create()->id;
        },
        'flooring_type_id'   => function () {
            return factory(FlooringType::class)->create()->id;
        },
        'name'               => $faker->word,
        'total_sqm'          => $faker->randomFloat(2, 10, 50),
        'affected_sqm'       => $faker->randomFloat(2, 5, 25),
        'non_restorable_sqm' => $faker->randomFloat(2, 1, 10),
    ];
});
