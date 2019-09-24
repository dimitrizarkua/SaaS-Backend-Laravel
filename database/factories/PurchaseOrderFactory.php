<?php

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Locations\Models\Location;
use Faker\Generator as Faker;
use App\Components\Finance\Models\PurchaseOrderStatus;
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
$factory->define(PurchaseOrder::class, function (Faker $faker) {
    return [
        'location_id'                => function () {
            return factory(Location::class)->create()->id;
        },
        'accounting_organization_id' => function () {
            return factory(AccountingOrganization::class)->create()->id;
        },
        'recipient_contact_id'       => function () {
            return factory(\App\Components\Contacts\Models\Contact::class)->create()->id;
        },
        'recipient_address'          => $faker->address,
        'recipient_name'             => $faker->name,
        'job_id'                     => null,
        'document_id'                => null,
        'date'                       => $faker->date(),
        'reference'                  => $faker->word,
    ];
});

$factory->afterCreating(PurchaseOrder::class, function (PurchaseOrder $purchaseOrder) {
    factory(PurchaseOrderStatus::class)->create([
        'purchase_order_id' => $purchaseOrder->id,
        'status'            => FinancialEntityStatuses::DRAFT,
    ]);
});
