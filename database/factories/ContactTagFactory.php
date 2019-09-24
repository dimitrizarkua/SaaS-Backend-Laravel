<?php

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Tags\Enums\TagTypes;
use App\Components\Tags\Models\Tag;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ContactTag::class, function () {
    return [
        'contact_id' => function () {
            return factory(Contact::class)->create()->id;
        },
        'tag_id'     => function () {
            return factory(Tag::class)->create([
                'type' => TagTypes::CONTACT,
            ])->id;
        },
    ];
});
