<?php

use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionImage;
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
$factory->define(AssessmentReportSectionImage::class, function (Faker $faker) {
    return [
        'assessment_report_section_id' => function () use ($faker) {
            return factory(AssessmentReportSection::class)->create([
                'type' => AssessmentReportSectionTypes::IMAGE,
            ])->id;
        },
        'photo_id'                     => function () use ($faker) {
            return factory(Photo::class)->create()->id;
        },
        'caption'                      => $faker->text(),
        'desired_width'                => $faker->numberBetween(256, 4096),
    ];
});
