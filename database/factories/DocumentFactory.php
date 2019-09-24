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
$factory->define(\App\Components\Documents\Models\Document::class, function (Faker $faker) {
    return [
        'storage_uid' => $faker->uuid,
        'file_name'   => $faker->word . '.' . $faker->fileExtension,
        'file_size'   => $faker->numberBetween(10),
        'file_hash'   => $faker->sha256,
        'mime_type'   => $faker->mimeType,
    ];
});
