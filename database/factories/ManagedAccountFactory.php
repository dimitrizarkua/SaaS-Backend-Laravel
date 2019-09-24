<?php

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ManagedAccount;
use App\Models\User;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ManagedAccount::class, function () {
    return [
        'user_id'    => function () {
            return factory(User::class)->create()->id;
        },
        'contact_id' => function () {
            return factory(Contact::class)->create()->id;
        },
        'created_at' => Carbon::now(),
    ];
});
