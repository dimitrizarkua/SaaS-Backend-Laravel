<?php

namespace Tests\API\SiteSurvey;

use App\Components\Jobs\Models\JobSiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\Jobs\JobTestCase;

/**
 * Class JobSiteSurveyControllerTest
 *
 * @package Tests\API\SiteSurvey
 * @group   site-survey
 * @group   jobs
 * @group   api
 */
class JobSiteSurveyControllerTest extends JobTestCase
{
    protected $permissions = ['jobs.view', 'jobs.update'];

    public function testGetSiteSurvey()
    {
        $job = $this->fakeJobWithStatus();
        $url = action('Jobs\JobSiteSurveyController@getSiteSurvey', [
            'job_id' => $job->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSee('allQuestions')
            ->assertSee('jobQuestions')
            ->assertSee('jobRooms');
    }

    public function testAttachQuestionToJob()
    {
        $job = $this->fakeJobWithStatus();
        /** @var SiteSurveyQuestionOption $questionOption */
        $questionOption = factory(SiteSurveyQuestionOption::class)->create();
        $url            = action('Jobs\JobSiteSurveyController@attachQuestion', [
            'job_id'      => $job->id,
            'question_id' => $questionOption->site_survey_question_id,
        ]);
        $fields         = [
            'site_survey_question_option_id' => $questionOption->id,
        ];

        $this->postJson($url, $fields)->assertStatus(200);

        JobSiteSurveyQuestion::query()->where([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $questionOption->site_survey_question_id,
        ])->firstOrFail();
    }

    public function testDetachQuestionFromJob()
    {
        $job = $this->fakeJobWithStatus();
        /** @var SiteSurveyQuestion $question */
        $question = factory(SiteSurveyQuestion::class)->create();

        JobSiteSurveyQuestion::insert([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $question->id,
        ]);

        $url = action('Jobs\JobSiteSurveyController@detachQuestion', [
            'job_id'      => $job->id,
            'question_id' => $question->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        JobSiteSurveyQuestion::query()->where([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $question->id,
        ])->firstOrFail();
    }
}
