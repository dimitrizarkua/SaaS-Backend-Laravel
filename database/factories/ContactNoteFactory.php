<?php

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Meetings\Models\Meeting;
use App\Components\Notes\Models\Note;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ContactNote::class, function () {
    return [
        'contact_id' => function () {
            return factory(Contact::class)->create()->id;
        },
        'note_id'    => function () {
            return factory(Note::class)->create()->id;
        },
        'meeting_id' => function () {
            return factory(Meeting::class)->create()->id;
        },
    ];
});
