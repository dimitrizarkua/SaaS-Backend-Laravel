<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\TaxRate;

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
$factory->define(GLAccount::class, function (Faker $faker) {
    return [
        'accounting_organization_id' => function () {
            return factory(AccountingOrganization::class)->create()->id;
        },
        'account_type_id'            => function () {
            return factory(AccountType::class)->create()->id;
        },
        'tax_rate_id'                => function () {
            return factory(TaxRate::class)->create()->id;
        },
        'code'                       => $faker->word,
        'name'                       => $faker->word,
        'description'                => $faker->sentence,
        'bank_account_name'          => $faker->word,
        'bank_account_number'        => $faker->bankAccountNumber,
        'bank_bsb'                   => $faker->word,
        'enable_payments_to_account' => $faker->boolean,
        'status'                     => $faker->word,
        'is_active'                  => $faker->boolean,
    ];
});
