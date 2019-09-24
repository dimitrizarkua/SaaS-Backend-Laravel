<?php

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\Enums\ContactTypes;
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
$factory->define(ContactCompanyProfile::class, function (Faker $faker) {
    return [
        'contact_id'                 => function () {
            return factory(Contact::class)->create([
                'contact_type' => ContactTypes::COMPANY,
            ])->id;
        },
        'legal_name'                 => $faker->word,
        'trading_name'               => $faker->word,
        'abn'                        => $faker->word,
        'website'                    => $faker->url,
        'default_payment_terms_days' => $faker->numberBetween(1, 30),
    ];
});
