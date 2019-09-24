<?php

use App\Components\Auth\Interfaces\ForgotPasswordServiceInterface;
use App\Enums\UserTokenTypes;
use App\Models\User;
use App\Models\UserToken;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
$factory->define(UserToken::class, function (Faker $faker) {
    return [
        'user_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'type'       => $faker->randomElement(UserTokenTypes::values()),
        'token'      => Str::random(64),
        'created_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addHour(ForgotPasswordServiceInterface::LINK_LIFE_TIME),
    ];
});
