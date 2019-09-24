<?php

use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\Payment;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(InvoicePayment::class, function (Faker $faker) {
    return [
        'payment_id' => function () {
            return factory(Payment::class)->create()->id;
        },
        'invoice_id' => function () {
            return factory(Invoice::class)->create()->id;
        },
        'amount'     => $faker->randomFloat(2, 1, 10),
        'is_fp'      => $faker->boolean,
    ];
});
