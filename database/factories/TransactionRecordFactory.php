<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\GLAccount;

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
$factory->define(TransactionRecord::class, function (Faker $faker) {
    return [
        'transaction_id' => function () {
            return factory(Transaction::class)->create()->id;
        },
        'gl_account_id'  => function () {
            return factory(GLAccount::class)->create()->id;
        },
        'amount'         => $faker->randomFloat(2, 0, 10000000),
        'is_debit'       => $faker->boolean,
    ];
});
