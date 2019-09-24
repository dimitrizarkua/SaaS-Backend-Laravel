<?php

use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\AccountType;
use Faker\Generator as Faker;


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(AccountType::class, function (Faker $faker) {
    return [
        'name'                     => $faker->name,
        'increase_action_is_debit' => $faker->boolean,
        'show_on_pl'               => $faker->boolean,
        'show_on_bs'               => $faker->boolean,
        'account_type_group_id'    => function () {
            return factory(AccountTypeGroup::class)->create()->id;
        },
    ];
});
