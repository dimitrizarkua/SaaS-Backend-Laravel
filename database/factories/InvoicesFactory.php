<?php

use Faker\Generator as Faker;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\Invoice;
use App\Components\Locations\Models\Location;
use App\Components\Jobs\Models\Job;
use App\Components\Documents\Models\Document;
use Illuminate\Support\Carbon;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Enums\FinancialEntityStatuses;

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
$factory->define(Invoice::class, function (Faker $faker) {
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
        'document_id'                => function () {
            return factory(Document::class)->create()->id;
        },
        'date'                       => Carbon::now(),
        'due_at'                     => Carbon::now()->addDays(3),
        'created_at'                 => Carbon::now(),
        'reference'                  => $faker->word,
    ];
});

$factory->afterCreating(Invoice::class, function (Invoice $invoice) {
    factory(InvoiceStatus::class)->create([
        'invoice_id' => $invoice->id,
        'status'     => FinancialEntityStatuses::DRAFT,
    ]);
});
