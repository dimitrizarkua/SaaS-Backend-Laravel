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
$factory->define(\App\Components\RBAC\Models\Role::class, function (Faker $faker) {
    return [
        'name'         => $faker->unique()->word,
        'description'  => $faker->paragraph,
        'display_name' => $faker->word,
    ];
});
