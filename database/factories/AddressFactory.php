<?php

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Suburb;
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
$factory->define(Address::class, function (Faker $faker) {
    return [
        'contact_name'   => $faker->name,
        'contact_phone'  => $faker->phoneNumber,
        'suburb_id'      => function () {
            return factory(Suburb::class)->create()->id;
        },
        'address_line_1' => $faker->streetAddress,
        'address_line_2' => $faker->streetAddress,
    ];
});
