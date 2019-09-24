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
$factory->define(\App\Components\Contacts\Models\ContactPersonProfile::class, function (Faker $faker) {
    return [
        'contact_id'   => function () {
            return factory(\App\Components\Contacts\Models\Contact::class)->create([
                'contact_type' => \App\Components\Contacts\Models\Enums\ContactTypes::PERSON,
            ])->id;
        },
        'first_name'   => $faker->firstName,
        'last_name'    => $faker->lastName,
        'job_title'    => $faker->jobTitle,
        'direct_phone' => $faker->phoneNumber,
        'mobile_phone' => $faker->phoneNumber,
    ];
});
