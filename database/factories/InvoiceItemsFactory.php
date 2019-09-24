<?php

use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
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
$factory->define(InvoiceItem::class, function (Faker $faker) {
    return [
        'invoice_id'    => function () {
            return factory(Invoice::class)->create()->id;
        },
        'gs_code_id'    => function () {
            return factory(GSCode::class)->create()->id;
        },
        'description'   => $faker->word,
        'unit_cost'     => $faker->randomFloat(2, 100, 1000),
        'quantity'      => $faker->numberBetween(1, 4),
        'discount'      => $faker->randomFloat(2, 10, 50),
        'gl_account_id' => function () {
            return factory(GLAccount::class)->create()->id;
        },
        'tax_rate_id'   => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'position'      => $faker->numberBetween(1, 50),
    ];
});
