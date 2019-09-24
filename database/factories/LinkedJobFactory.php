<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\LinkedJob;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(LinkedJob::class, function () {
    return [
        'job_id'  => function () {
            return factory(Job::class)->create()->id;
        },
        'linked_job_id' => function () {
            return factory(Job::class)->create()->id;
        },
    ];
});
