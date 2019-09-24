<?php

use App\Components\Contacts\Models\Enums\ContactTypes;
use Faker\Generator as Faker;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Contacts\Models\Contact;

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
$factory->define(AccountingOrganization::class, function (Faker $faker) {
    return [
        'contact_id'                     => function () {
            return factory(Contact::class)->create(['contact_type' => ContactTypes::COMPANY])->id;
        },
        'tax_payable_account_id'         => null,
        'tax_receivable_account_id'      => null,
        'accounts_payable_account_id'    => null,
        'accounts_receivable_account_id' => null,
        'payment_details_account_id'     => null,
        'cc_payments_api_key'            => $faker->randomKey(),
        'is_active'                      => $faker->boolean,
        'lock_day_of_month'              => $this->faker->numberBetween(1, 31),
    ];
});
