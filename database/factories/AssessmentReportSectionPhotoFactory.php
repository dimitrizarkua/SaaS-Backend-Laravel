<?php

use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\Photos\Models\Photo;
use Faker\Generator as Faker;

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
$factory->define(AssessmentReportSectionPhoto::class, function (Faker $faker) {
    return [
        'assessment_report_section_id' => function () use ($faker) {
            return factory(AssessmentReportSection::class)->create([
                'type' => AssessmentReportSectionTypes::PHOTOS,
            ])->id;
        },
        'photo_id'                     => function () use ($faker) {
            return factory(Photo::class)->create()->id;
        },
        'position'                     => $faker->numberBetween(1, 100),
        'caption'                      => $faker->text(),
    ];
});
