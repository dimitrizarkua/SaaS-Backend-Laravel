<?php

use Faker\Generator as Faker;
use App\Components\Contacts\Models\Contact;
use App\Components\UsageAndActuals\Models\InsurerContract;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(InsurerContract::class, function (Faker $faker) {
    return [
        'contact_id' => function () {
            return factory(Contact::class)->create()->id;
        },
        'contract_number' => $faker->bankAccountNumber,
        'description'    => $this->faker->text,
        'effect_date'     => Carbon::now()->format('Y-m-d'),
        'termination_date'  => Carbon::now()->addDays($faker->numberBetween(1, 100))->format('Y-m-d'),
    ];
});
