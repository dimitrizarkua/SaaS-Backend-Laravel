<?php

use Faker\Generator as Faker;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\Enums\ContactStatuses;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Contact::class, function (Faker $faker) {
    return [
        'contact_type'        => $faker->randomElement(ContactTypes::values()),
        'contact_category_id' => function () {
            return factory(ContactCategory::class)->create()->id;
        },
        'email'               => $faker->email,
        'business_phone'      => $faker->phoneNumber,
        'last_active_at'      => $faker->date(),
    ];
});

$factory->afterCreating(Contact::class, function (Contact $contact) {
    switch ($contact->contact_type) {
        case ContactTypes::COMPANY:
            $class = ContactCompanyProfile::class;
            break;
        case ContactTypes::PERSON:
            $class = ContactPersonProfile::class;
            break;
    }

    factory($class)->create(['contact_id' => $contact->id]);

    factory(ContactStatus::class)->create([
        'contact_id' => $contact->id,
        'status'     => ContactStatuses::ACTIVE,
        'created_at' => $contact->created_at,
    ]);
});
