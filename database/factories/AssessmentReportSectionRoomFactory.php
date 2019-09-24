<?php

use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionRoom;
use App\Components\AssessmentReports\Models\CarpetAge;
use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Components\AssessmentReports\Models\CarpetType;
use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Components\AssessmentReports\Models\UnderlayType;
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
$factory->define(AssessmentReportSectionRoom::class, function (Faker $faker) {
    return [
        'assessment_report_section_id' => function () use ($faker) {
            return factory(AssessmentReportSection::class)->create([
                'type' => AssessmentReportSectionTypes::ROOM,
            ])->id;
        },
        'name'                         => $faker->words(3, true),
        'flooring_type_id'             => function () use ($faker) {
            return factory(FlooringType::class)->create()->id;
        },
        'flooring_subtype_id'          => function () use ($faker) {
            return factory(FlooringSubtype::class)->create()->id;
        },
        'dimensions_length'            => $faker->randomFloat(2, 1, 500),
        'dimensions_width'             => $faker->randomFloat(2, 1, 500),
        'dimensions_height'            => $faker->randomFloat(2, 1, 500),
        'dimensions_affected_length'   => $faker->randomFloat(2, 1, 500),
        'dimensions_affected_width'    => $faker->randomFloat(2, 1, 500),
        'underlay_required'            => true,
        'underlay_type_id'             => function () use ($faker) {
            return factory(UnderlayType::class)->create()->id;
        },
        'underlay_type_note'           => $faker->text(),
        'dimensions_underlay_length'   => $faker->randomFloat(2, 1, 500),
        'dimensions_underlay_width'    => $faker->randomFloat(2, 1, 500),
        'trims_required'               => true,
        'trim_type'                    => $faker->words(2, true),
        'restorable'                   => true,
        'non_restorable_reason_id'     => function () use ($faker) {
            return factory(NonRestorableReason::class)->create()->id;
        },
        'carpet_type_id'               => function () use ($faker) {
            return factory(CarpetType::class)->create()->id;
        },
        'carpet_construction_type_id'  => function () use ($faker) {
            return factory(CarpetConstructionType::class)->create()->id;
        },
        'carpet_age_id'                => function () use ($faker) {
            return factory(CarpetAge::class)->create()->id;
        },
        'carpet_face_fibre_id'         => function () use ($faker) {
            return factory(CarpetFaceFibre::class)->create()->id;
        },
    ];
});
