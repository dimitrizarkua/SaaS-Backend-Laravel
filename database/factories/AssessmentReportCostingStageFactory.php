<?php

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
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
$factory->define(AssessmentReportCostingStage::class, function (Faker $faker) {
    return [
        'assessment_report_id' => function () {
            return factory(AssessmentReport::class)->create()->id;
        },
        'name'                 => $faker->word,
        'position'             => $faker->numberBetween(1, 100),
    ];
});
