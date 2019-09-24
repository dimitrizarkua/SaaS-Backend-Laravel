<?php

use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ContactStatus::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(ContactStatuses::values()),
    ];
});
