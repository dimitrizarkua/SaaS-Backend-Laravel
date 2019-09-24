<?php

use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
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
$factory->define(AssessmentReportSectionCostItem::class, function (Faker $faker) {
    return [
        'assessment_report_section_id'   => function () use ($faker) {
            return factory(AssessmentReportSection::class)->create([
                'type' => AssessmentReportSectionTypes::COSTS,
            ])->id;
        },
        'assessment_report_cost_item_id' => function () use ($faker) {
            return factory(AssessmentReportCostItem::class)->create()->id;
        },
        'position'                       => $faker->numberBetween(1, 100),
    ];
});
