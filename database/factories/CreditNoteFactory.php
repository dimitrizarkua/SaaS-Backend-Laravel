<?php

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
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
$factory->define(CreditNote::class, function (Faker $faker) {
    return [
        'location_id'                => function () {
            return factory(Location::class)->create()->id;
        },
        'accounting_organization_id' => function () {
            return factory(AccountingOrganization::class)->create()->id;
        },
        'recipient_contact_id'       => function () {
            return factory(Contact::class)->create()->id;
        },
        'recipient_address'          => $faker->address,
        'recipient_name'             => $faker->name,
        'job_id'                     => function () {
            return factory(Job::class)->create()->id;
        },
        'payment_id'                 => function () {
            return factory(Payment::class)->create()->id;
        },
        'date'                       => Carbon::now()->addDays($faker->numberBetween(1, 5)),
        'locked_at'                  => null,
    ];
});

$factory->afterCreating(CreditNote::class, function (CreditNote $creditNote) {
    factory(CreditNoteStatus::class)->create([
        'credit_note_id' => $creditNote->id,
        'status'         => FinancialEntityStatuses::DRAFT,
    ]);
});
