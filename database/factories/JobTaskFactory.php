<?php

use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskStatus;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

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
$factory->define(\App\Components\Jobs\Models\JobTask::class, function (Faker $faker) {
    return [
        'job_id'            => function () {
            return factory(\App\Components\Jobs\Models\Job::class)->create()->id;
        },
        'job_task_type_id'  => function () {
            return factory(\App\Components\Jobs\Models\JobTaskType::class)->create()->id;
        },
        'job_run_id'        => null,
        'name'              => $faker->word,
        'internal_note'     => $faker->sentence,
        'scheduling_note'   => $faker->sentence,
        'kpi_missed_reason' => $faker->sentence,
        'due_at'            => $this->faker->date('Y-m-d\TH:i:s\Z'),
        'starts_at'         => Carbon::now()->subDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        'ends_at'           => Carbon::now()->addDays($this->faker->randomNumber(1))->format('Y-m-d\TH:i:s\Z'),
        'snoozed_until'     => null,
    ];
});

/**
 * Creates active status if it doesn't exists.
 *
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */

$factory->afterCreating(JobTask::class, function (JobTask $jobTask) {
    factory(JobTaskStatus::class)->create([
        'job_task_id' => $jobTask->id,
    ]);
});
