<?php

namespace Tests\API\SiteSurvey;

use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
use Tests\API\ApiTestCase;

/**
 * Class SiteSurveyQuestionOptionsControllerTest
 *
 * @package Tests\API\SiteSurvey
 * @group   site-survey
 * @group   api
 */
class SiteSurveyQuestionOptionsControllerTest extends ApiTestCase
{
    protected $permissions = ['management.system.settings'];

    public function testGetAllSiteSurveyQuestionOptions()
    {
        /** @var SiteSurveyQuestion $siteSurveyQuestion */
        $siteSurveyQuestion = factory(SiteSurveyQuestion::class)->create();
        $countOfRecords     = $this->faker->numberBetween(1, 5);
        factory(SiteSurveyQuestionOption::class, $countOfRecords)->create([
            'site_survey_question_id' => $siteSurveyQuestion->id,
        ]);

        $url = action('Management\SiteSurveyQuestionOptionsController@index', [
            'question' => $siteSurveyQuestion->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetOneSiteSurveyQuestionOptions()
    {
        /** @var SiteSurveyQuestionOption $siteSurveyQuestionOption */
        $siteSurveyQuestionOption = factory(SiteSurveyQuestionOption::class)->create();
        $url                      = action('Management\SiteSurveyQuestionOptionsController@show', [
            'question'        => $siteSurveyQuestionOption->site_survey_question_id,
            'question_option' => $siteSurveyQuestionOption->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertEquals($data['id'], $siteSurveyQuestionOption->id);
        self::assertEquals($data['name'], $siteSurveyQuestionOption->name);
        self::assertEquals($data['site_survey_question_id'], $siteSurveyQuestionOption->site_survey_question_id);
    }

    public function testCreateSiteSurveyQuestionOption()
    {
        /** @var SiteSurveyQuestion $siteSurveyQuestion */
        $siteSurveyQuestion = factory(SiteSurveyQuestion::class)->create();
        $url                = action('Management\SiteSurveyQuestionOptionsController@store', [
            'question' => $siteSurveyQuestion->id,
        ]);
        $request            = [
            'name' => $this->faker->word,
        ];

        $response = $this->postJson($url, $request);
        $response->assertStatus(201)
            ->assertSeeData();

        $data     = $response->getData();
        $reloaded = SiteSurveyQuestionOption::findOrFail($data['id']);
        self::assertEquals($siteSurveyQuestion->id, $reloaded->site_survey_question_id);
        self::assertEquals($request['name'], $reloaded->name);
    }

    public function testUpdateSiteSurveyQuestionOption()
    {
        /** @var SiteSurveyQuestionOption $siteSurveyQuestionOption */
        $siteSurveyQuestionOption = factory(SiteSurveyQuestionOption::class)->create();
        $url                      = action('Management\SiteSurveyQuestionOptionsController@update', [
            'question'        => $siteSurveyQuestionOption->site_survey_question_id,
            'question_option' => $siteSurveyQuestionOption->id,
        ]);
        $request                  = [
            'name'                    => $this->faker->word,
        ];
        $response = $this->patchJson($url, $request);
        $response->assertStatus(200)
            ->assertSeeData();

        $data     = $response->getData();
        $reloaded = SiteSurveyQuestionOption::findOrFail($data['id']);
        self::assertEquals($siteSurveyQuestionOption->site_survey_question_id, $reloaded->site_survey_question_id);
        self::assertEquals($request['name'], $reloaded->name);
    }
}
