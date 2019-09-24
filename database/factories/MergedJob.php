<?php

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\MergedJob;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(MergedJob::class, function () {
    return [
        'source_job_id'      => function () {
            return factory(Job::class)->create()->id;
        },
        'destination_job_id' => function () {
            return factory(Job::class)->create()->id;
        },
    ];
});
