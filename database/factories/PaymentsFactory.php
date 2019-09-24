<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

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
$factory->define(Payment::class, function (Faker $faker) {
    return [
        'type'           => $faker->randomElement(PaymentTypes::values()),
        'transaction_id' => function () {
            return factory(Transaction::class)->create()->id;
        },
        'user_id'        => function () {
            return factory(User::class)->create()->id;
        },
        'amount'         => $faker->randomFloat(2, 0, 10),
        'paid_at'        => Carbon::now(),
        'created_at'     => Carbon::now(),
        'updated_at'     => Carbon::now(),
    ];
});
