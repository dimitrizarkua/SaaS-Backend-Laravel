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
$factory->define(\App\Components\Jobs\Models\JobTag::class, function (Faker $faker) {
    return [
        'job_id' => function () {
            return factory(\App\Components\Jobs\Models\Job::class)->create()->id;
        },
        'tag_id' => function () {
            return factory(\App\Components\Tags\Models\Tag::class)->create()->id;
        },
    ];
});
