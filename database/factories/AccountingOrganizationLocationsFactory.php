<?php

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Locations\Models\Location;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(AccountingOrganizationLocation::class, function () {
    return [
        'accounting_organization_id' => function () {
            return factory(AccountingOrganization::class)->create()->id;
        },
        'location_id'                => function () {
            return factory(Location::class)->create()->id;
        },
    ];
});
