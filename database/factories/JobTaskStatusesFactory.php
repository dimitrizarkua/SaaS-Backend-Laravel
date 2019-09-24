<?php

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskStatus;
use App\Models\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobTaskStatus::class, function () {
    return [
        'user_id'     => function () {
            return factory(User::class)->create()->id;
        },
        'job_task_id' => function () {
            return factory(JobTask::class)->create()->id;
        },
        'status'      => JobTaskStatuses::ACTIVE,
    ];
});
