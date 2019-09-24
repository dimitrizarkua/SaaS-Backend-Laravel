<?php

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Models\User;
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
$factory->define(PurchaseOrderApproveRequest::class, function (Faker $faker) {
    return [
        'purchase_order_id' => function () {
            return factory(PurchaseOrder::class)->create()->id;
        },
        'approver_id'       => function () {
            return factory(User::class)->create()->id;
        },
        'requester_id'      => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
