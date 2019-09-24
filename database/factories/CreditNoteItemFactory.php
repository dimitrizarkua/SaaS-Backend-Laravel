<?php

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
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
$factory->define(CreditNoteItem::class, function (Faker $faker) {
    return [
        'credit_note_id' => function () {
            return factory(CreditNote::class)->create()->id;
        },
        'gs_code_id'     => function () {
            return factory(GSCode::class)->create()->id;
        },
        'gl_account_id'  => function () {
            return factory(GLAccount::class)->create()->id;
        },
        'tax_rate_id'    => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'description'    => $faker->text,
        'quantity'       => $faker->numberBetween(1, 5),
        'unit_cost'      => $faker->randomFloat(2, 10, 1000),
        'position'       => $faker->numberBetween(1, 50),
    ];
});
