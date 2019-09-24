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
$factory->define(\App\Components\Jobs\Models\JobContactAssignment::class, function (Faker $faker) {
    return [
        'job_id'                 => function () {
            return factory(\App\Components\Jobs\Models\Job::class)->create()->id;
        },
        'job_assignment_type_id' => function () {
            return factory(\App\Components\Jobs\Models\JobContactAssignmentType::class)->create()->id;
        },
        'assignee_contact_id'    => function () {
            return factory(\App\Components\Contacts\Models\Contact::class)->create()->id;
        },
        'invoice_to'             => $faker->boolean,
    ];
});
