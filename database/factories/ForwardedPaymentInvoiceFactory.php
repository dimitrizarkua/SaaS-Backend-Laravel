<?php

use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\Invoice;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ForwardedPaymentInvoice::class, function (Faker $faker) {
    return [
        'forwarded_payment_id' => function () {
            return factory(ForwardedPayment::class)->create()->id;
        },
        'invoice_id'           => function () {
            return factory(Invoice::class)->create()->id;
        },
        'amount'               => $faker->randomFloat(2, 1, 10),
    ];
});
