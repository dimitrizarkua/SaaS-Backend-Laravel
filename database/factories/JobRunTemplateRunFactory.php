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
$factory->define(\App\Components\Operations\Models\JobRunTemplateRun::class, function (Faker $faker) {
    return [
        'job_run_template_id' => function () {
            return factory(\App\Components\Operations\Models\JobRunTemplate::class)->create()->id;
        },
        'name'                => $faker->word,
    ];
});
