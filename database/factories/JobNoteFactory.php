<?php

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
$factory->define(\App\Components\Jobs\Models\JobNote::class, function (Faker $faker) {
    return [
        'job_id'        => function () {
            return factory(\App\Components\Jobs\Models\Job::class)->create()->id;
        },
        'note_id'       => function () {
            return factory(\App\Components\Notes\Models\Note::class)->create()->id;
        },
        'job_status_id' => null,
    ];
});
