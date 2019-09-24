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

$permissions     = config('rbac.permissions', []);
$permissionNames = array_map(function ($permission) {
    return $permission['name'];
}, $permissions);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Components\RBAC\Models\PermissionRole::class, function (Faker $faker) use ($permissionNames) {
    return [
        'role_id'    => $faker->unique()->randomDigitNotNull,
        'permission' => $faker->unique()->randomElement($permissionNames),
    ];
});
