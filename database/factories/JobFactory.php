<?php

use App\Components\Addresses\Models\Address;
use App\Components\Jobs\Enums\ClaimTypes;
use App\Components\Jobs\Enums\JobCriticalityTypes;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Models\InsurerContract;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Enums\JobStatuses;

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
$factory->define(Job::class, function (Faker $faker) {
    $insurer  = factory(Contact::class)->create([
        'contact_type' => ContactTypes::COMPANY,
    ]);
    $contract = factory(InsurerContract::class)->create([
        'contact_id' => $insurer->id,
    ]);

    return [
        'claim_number'             => $faker->unique()->regexify('[A-Z0-9]{5,10}'),
        'job_service_id'           => function () {
            return factory(JobService::class)->create()->id;
        },
        'description'              => $faker->sentence,
        'touched_at'               => $faker->dateTimeInInterval(
            Carbon::yesterday(),
            Carbon::now()
        ),
        'insurer_id'               => $insurer->id,
        'insurer_contract_id'      => $contract->id,
        'site_address_id'          => function () {
            return factory(Address::class)->create()->id;
        },
        'site_address_lat'         => $faker->numberBetween(-90, +90),
        'site_address_lng'         => $faker->numberBetween(-180, +180),
        'assigned_location_id'     => function () {
            return factory(Location::class)->create()->id;
        },
        'owner_location_id'        => function () {
            return factory(Location::class)->create()->id;
        },
        'reference_number'         => $faker->word,
        'claim_type'               => $faker->randomElement(ClaimTypes::values()),
        'criticality'              => $faker->randomElement(JobCriticalityTypes::values()),
        'date_of_loss'             => $faker->date(),
        'initial_contact_at'       => $faker->date(),
        'cause_of_loss'            => $faker->word,
        'anticipated_revenue'      => $faker->randomFloat(2),
        'anticipated_invoice_date' => $faker->date(),
        'authority_received_at'    => $faker->date(),
        'expected_excess_payment'  => $faker->randomFloat(2),
        'created_at'               => $faker->date(),
        'pinned_at'                => null,
        'recurring_job_id'         => function () {
            return factory(RecurringJob::class)->create()->id;
        },
    ];
});

$factory->afterCreating(Job::class, function (Job $job) {
    factory(JobStatus::class)->create([
        'user_id'    => null,
        'job_id'     => $job->id,
        'status'     => JobStatuses::NEW,
        'created_at' => $job->created_at,
    ]);
});

