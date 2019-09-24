<?php

use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
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
$factory->define(SiteSurveyQuestionOption::class, function (Faker $faker) {
    return [
        'site_survey_question_id' => function () {
            return factory(SiteSurveyQuestion::class)->create([
                'is_active' => true,
            ])->id;
        },
        'name'                    => $faker->unique()->sentence(2),
    ];
});
