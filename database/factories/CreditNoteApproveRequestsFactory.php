<?php

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteApproveRequest;
use App\Models\User;
use Faker\Generator as Faker;
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
$factory->define(CreditNoteApproveRequest::class, function (Faker $faker) {
    return [
        'requester_id'   => function () {
            return factory(User::class)->create()->id;
        },
        'approver_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'credit_note_id' => function () {
            return factory(CreditNote::class)->create()->id;
        },
        'approved_at'    => Carbon::now(),
    ];
});
