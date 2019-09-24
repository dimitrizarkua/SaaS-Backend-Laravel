<?php

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Models\InsurerContract;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(RecurringJob::class, function (Faker $faker) {
    $insurer = factory(Contact::class)->create([
        'contact_type' => ContactTypes::COMPANY,
    ]);
    factory(InsurerContract::class)->create([
        'contact_id' => $insurer->id,
    ]);

    return [
        'insurer_id'        => $insurer->id,
        'job_service_id'    => function () {
            return factory(JobService::class)->create()->id;
        },
        'site_address_id'   => function () {
            return factory(Address::class)->create()->id;
        },
        'owner_location_id' => function () {
            return factory(Location::class)->create()->id;
        },
        'description'       => $faker->sentence,
        'recurrence_rule'   => $faker->sentence,
    ];
});
