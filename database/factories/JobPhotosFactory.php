<?php


use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobPhoto;
use App\Components\Photos\Models\Photo;
use Faker\Generator as Faker;
use App\Models\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobPhoto::class, function (Faker $faker) {
    return [
        'job_id'         => function () {
            return factory(Job::class)->create()->id;
        },
        'photo_id'       => function () {
            return factory(Photo::class)->create()->id;
        },
        'creator_id'     => function () {
            return factory(User::class)->create()->id;
        },
        'modified_by_id' => function () {
            return factory(User::class)->create()->id;
        },
        'description'    => $faker->sentence(2),
    ];
});
