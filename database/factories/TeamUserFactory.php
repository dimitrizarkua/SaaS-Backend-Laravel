<?php

use App\Components\Teams\Models\TeamMember;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TeamMember::class, function () {
    return [
        'team_id' => function () {
            return factory(\App\Components\Teams\Models\Team::class)->create()->id;
        },
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
    ];
});
