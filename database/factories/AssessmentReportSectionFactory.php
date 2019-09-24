<?php

use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
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
$factory->define(AssessmentReportSection::class, function (Faker $faker) {
    return [
        'assessment_report_id' => function () {
            return factory(AssessmentReport::class)->create()->id;
        },
        'type'                 => $faker->randomElement(AssessmentReportSectionTypes::values()),
        'position'             => $faker->numberBetween(1, 100),
        'heading'              => $faker->text(),
        'heading_style'        => $faker->randomElement(AssessmentReportHeadingStyles::values()),
        'heading_color'        => $faker->numberBetween(0, 16777215),
        'text'                 => $faker->text(),
    ];
});
