<?php

use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
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
$factory->define(PurchaseOrderItem::class, function (Faker $faker) {
    return [
        'purchase_order_id' => function () {
            return factory(PurchaseOrder::class)->create()->id;
        },
        'gs_code_id'        => function () {
            return factory(GSCode::class)->create()->id;
        },
        'description'       => $faker->sentence,
        'unit_cost'         => $faker->randomFloat(2, 1, 1000),
        'quantity'          => $faker->numberBetween(1, 10),
        'markup'            => $faker->numberBetween(1, 100),
        'gl_account_id'     => function () {
            return factory(GLAccount::class)->create()->id;
        },
        'tax_rate_id'       => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'position'          => $faker->numberBetween(1, 50),
    ];
});
