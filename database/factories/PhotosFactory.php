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
$factory->define(\App\Components\Photos\Models\Photo::class, function (Faker $faker) {
    $content = $faker->text;
    $hash    = hash('sha256', $content);

    return [
        'storage_uid'       => $hash,
        'file_name'         => $faker->word,
        'file_size'         => strlen($content),
        'file_hash'         => $hash,
        'mime_type'         => $faker->mimeType,
        'width'             => $faker->randomNumber(),
        'height'            => $faker->randomNumber(),
        'original_photo_id' => null,
        'created_at'        => $faker->date(),
    ];
});
