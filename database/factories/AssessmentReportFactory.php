<?php

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Jobs\Models\Job;
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
$factory->define(AssessmentReport::class, function (Faker $faker) {
    return [
        'job_id'     => function () {
            return factory(Job::class)->create()->id;
        },
        'user_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'heading'    => $faker->text(),
        'subheading' => $faker->text(),
        'date'       => $faker->date(),
    ];
});
