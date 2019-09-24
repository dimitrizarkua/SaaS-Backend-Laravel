<?php

use Illuminate\Database\Seeder;

/**
 * Class SiteSurveyQuestionsSeeder
 */
class SiteSurveyQuestionsSeeder extends Seeder
{
    private $questions = [
        [
            'name' => 'What was the cause of the damage?',
        ], [
            'name' => 'What date did the incident occur?',
        ], [
            'name'    => 'Has the source of damage been repaired?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name' => 'When have trades scheduled to attend?',
        ], [
            'name' => 'What rooms are affected and measurements?',
        ], [
            'name' => 'What type of flooring is affected?',
        ], [
            'name' => 'Is their underlay underneath or is the carpet glued down?',
        ], [
            'name'    => 'Do you have electricity on site?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Is there an odour or visible mould?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name' => 'What is the construction of the walls and subfloors?',
        ], [
            'name'    => 'Are the rooms open plan, or are there closing doors?',
            'options' => [
                'Open',
                'Closed',
            ],
        ], [
            'name'    => 'Has it gone underneath any cabinetry?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Have the skirting boards begun to swell?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Do you have evaporative cooling?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name' => 'What heating system do you have in your home?',
        ], [
            'name' => 'Parking available? Distance from parking to premises?',
        ], [
            'name' => 'Height restriction for parking?',
        ], [
            'name'    => 'Are there stairs onsite?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Is there an elevator on site?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'If required, is it ok for us to have a skip bin put on your property?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name' => 'How much furniture needs to be moved from each room, is it heavy furniture?',
        ], [
            'name'    => 'Are you able to start taking out items from furniture for ease of lifting?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Is there an unaffected area that furniture can be moved to?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'Is the property owner occupied or tenanted?',
            'options' => [
                'Owner',
                'Tenanted',
            ],
        ], [
            'name' => 'Do you have any contents items affected?',
        ], [
            'name'    => 'STORM: Are there any road closures to access your property?',
            'options' => [
                'Yes',
                'No',
            ],
        ], [
            'name'    => 'STORM: Has the SES made safe any structural damage at your property?',
            'options' => [
                'Yes',
                'No',
            ],
        ],
    ];

    /**
     * Seed the site survey questions and options
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->questions as $question) {
            /** @var \App\Components\SiteSurvey\Models\SiteSurveyQuestion $existing */
            $existing = DB::table('site_survey_questions')
                ->where('name', $question['name'])
                ->first();

            if (!$existing) {
                $questionId = DB::table('site_survey_questions')
                    ->insertGetId([
                        'name' => $question['name'],
                    ]);

                if (!empty($question['options'])) {
                    foreach ($question['options'] as $option) {
                        DB::table('site_survey_question_options')
                            ->insert([
                                'site_survey_question_id' => $questionId,
                                'name'                    => $option,
                            ]);
                    }
                }
            } elseif (!empty($question['options'])) {
                foreach ($question['options'] as $option) {
                    /** @var \App\Components\SiteSurvey\Models\SiteSurveyQuestionOption $existingOption */
                    $existingOption = DB::table('site_survey_question_options')
                        ->where('name', $option)
                        ->where('site_survey_question_id', $existing->id)
                        ->first();

                    if (!$existingOption) {
                        DB::table('site_survey_question_options')
                            ->insert([
                                'site_survey_question_id' => $existing->id,
                                'name'                    => $option,
                            ]);
                    }
                }
            }
        }
    }
}
