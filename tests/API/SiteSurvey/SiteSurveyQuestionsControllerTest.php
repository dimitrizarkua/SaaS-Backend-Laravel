<?php

namespace Tests\API\SiteSurvey;

use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use Tests\API\ApiTestCase;

/**
 * Class SiteSurveyQuestionsControllerTest
 *
 * @package Tests\API\SiteSurvey
 * @group   site-survey
 * @group   api
 */
class SiteSurveyQuestionsControllerTest extends ApiTestCase
{
    protected $permissions = ['management.system.settings'];

    public function testGetAllSiteSurveyQuestions()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);
        factory(SiteSurveyQuestion::class, $countOfRecords)->create();

        $url = action('Management\SiteSurveyQuestionsController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetOneSiteSurveyQuestion()
    {
        /** @var SiteSurveyQuestion $siteSurveyQuestion */
        $siteSurveyQuestion = factory(SiteSurveyQuestion::class)->create();
        $url                = action('Management\SiteSurveyQuestionsController@show', [
            'id' => $siteSurveyQuestion->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertEquals($data['id'], $siteSurveyQuestion->id);
        self::assertEquals($data['name'], $siteSurveyQuestion->name);
        self::assertEquals($data['is_active'], $siteSurveyQuestion->is_active);
    }

    public function testCreateSiteSurveyQuestion()
    {
        $url     = action('Management\SiteSurveyQuestionsController@store');
        $request = [
            'name'      => $this->faker->words(3, true),
            'is_active' => $this->faker->boolean,
        ];

        $response = $this->postJson($url, $request);
        $response->assertStatus(201)
            ->assertSeeData();

        $data     = $response->getData();
        $reloaded = SiteSurveyQuestion::findOrFail($data['id']);
        self::assertEquals($request['name'], $reloaded->name);
        self::assertEquals($request['is_active'], $reloaded->is_active);
    }

    public function testUpdateSiteSurveyQuestion()
    {
        /** @var SiteSurveyQuestion $siteSurveyQuestion */
        $siteSurveyQuestion = factory(SiteSurveyQuestion::class)->create();
        $url                = action('Management\SiteSurveyQuestionsController@update', [
            'id' => $siteSurveyQuestion->id,
        ]);
        $request            = [
            'name'      => $this->faker->words(3, true),
            'is_active' => $this->faker->boolean,
        ];

        $response = $this->patchJson($url, $request);
        $response->assertStatus(200)
            ->assertSeeData();

        $data     = $response->getData();
        $reloaded = SiteSurveyQuestion::findOrFail($data['id']);
        self::assertEquals($request['name'], $reloaded->name);
        self::assertEquals($request['is_active'], $reloaded->is_active);
    }
}
