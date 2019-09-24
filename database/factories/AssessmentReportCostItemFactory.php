<?php

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
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
$factory->define(AssessmentReportCostItem::class, function (Faker $faker) {
    return [
        'assessment_report_id'               => function () {
            return factory(AssessmentReport::class)->create()->id;
        },
        'assessment_report_costing_stage_id' => function () {
            return factory(AssessmentReportCostingStage::class)->create()->id;
        },
        'gs_code_id'                         => function () {
            return factory(GSCode::class)->create()->id;
        },
        'position'                           => $faker->numberBetween(1, 100),
        'description'                        => $faker->words(3, true),
        'quantity'                           => $faker->numberBetween(1, 10),
        'unit_cost'                          => $faker->randomFloat(2, 1, 500),
        'discount'                           => $faker->randomFloat(2, 1, 100),
        'markup'                             => $faker->randomFloat(2, 1, 200),
        'tax_rate_id'                        => function () use ($faker) {
            return factory(TaxRate::class)->create()->id;
        },
    ];
});
