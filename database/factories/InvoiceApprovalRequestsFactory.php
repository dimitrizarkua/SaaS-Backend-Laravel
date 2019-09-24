<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\Invoice;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Components\Finance\Models\InvoiceApproveRequest;

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
$factory->define(InvoiceApproveRequest::class, function (Faker $faker) {
    return [
        'invoice_id'   => function () {
            return factory(Invoice::class)->create()->id;
        },
        'requester_id' => function () {
            return factory(User::class)->create()->id;
        },
        'approver_id'  => function () {
            return factory(User::class)->create()->id;
        },
        'approved_at'  => Carbon::now(),
    ];
});
