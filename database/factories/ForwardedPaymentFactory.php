<?php

use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\Payment;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ForwardedPayment::class, function (Faker $faker) {
    return [
        'payment_id'           => function () {
            return factory(Payment::class)->create()->id;
        },
        'remittance_reference' => $faker->text,
        'transferred_at'       => $faker->dateTime,
    ];
});
