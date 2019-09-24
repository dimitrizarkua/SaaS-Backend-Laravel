<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobSiteSurveyServiceInterface;
use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\JobSiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobSiteSurveyServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   site-survey
 * @group   jobs
 * @group   services
 */
class JobSiteSurveyServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobSiteSurveyServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(JobSiteSurveyServiceInterface::class);
    }

    public function testGetSiteSurvey()
    {
        $job           = $this->fakeJobWithStatus();
        $questionCount = $this->faker->numberBetween(1, 3);
        factory(SiteSurveyQuestion::class, $questionCount)->create([
            'is_active' => true,
        ]);
        $jobQuestionCount          = $this->faker->numberBetween(1, 3);
        $siteSurveyQuestionOptions = factory(SiteSurveyQuestionOption::class, $jobQuestionCount)->create();
        /** @var SiteSurveyQuestionOption $questionOption */
        foreach ($siteSurveyQuestionOptions as $questionOption) {
            JobSiteSurveyQuestion::insert([
                'job_id'                         => $job->id,
                'site_survey_question_id'        => $questionOption->site_survey_question_id,
                'site_survey_question_option_id' => $questionOption->id,
            ]);
        }
        $jobRoomCount = $this->faker->numberBetween(1, 3);
        factory(JobRoom::class, $jobRoomCount)->create([
            'job_id' => $job->id,
        ]);

        $siteSurvey = $this->service->getSiteSurvey($job->id);

        self::objectHasAttribute('allQuestions');
        self::objectHasAttribute('jobQuestions');
        self::objectHasAttribute('jobRooms');
        self::assertCount($questionCount + $jobQuestionCount, $siteSurvey->allQuestions);
        self::assertCount($jobQuestionCount, $siteSurvey->jobQuestions);
        self::assertCount($jobRoomCount, $siteSurvey->jobRooms);
    }

    public function testGetEmptySiteSurvey()
    {
        $job = $this->fakeJobWithStatus();

        $siteSurvey = $this->service->getSiteSurvey($job->id);

        self::objectHasAttribute('allQuestions');
        self::objectHasAttribute('jobQuestions');
        self::objectHasAttribute('jobRooms');
        self::assertCount(0, $siteSurvey->allQuestions);
        self::assertCount(0, $siteSurvey->jobQuestions);
        self::assertCount(0, $siteSurvey->jobRooms);
    }

    public function testFailToGetSiteSurveyWhenJobNotFound()
    {
        self::expectException(ModelNotFoundException::class);
        $this->service->getSiteSurvey(0);
    }

    public function testAttachQuestionWithOptionToJob()
    {
        $job = $this->fakeJobWithStatus();
        /** @var SiteSurveyQuestionOption $questionOption */
        $questionOption = factory(SiteSurveyQuestionOption::class)->create();

        $this->service->attachQuestion($job->id, $questionOption->site_survey_question_id, $questionOption->id);

        JobSiteSurveyQuestion::query()->where([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $questionOption->site_survey_question_id,
        ])->firstOrFail();

        $job->load('siteSurveyQuestions');

        self::assertEquals(1, $job->siteSurveyQuestions()->count());
    }

    public function testFailToAttachQuestionWithNotOwnOptionToJob()
    {
        $job = $this->fakeJobWithStatus();
        /** @var SiteSurveyQuestion $question */
        $question = factory(SiteSurveyQuestion::class)->create();
        /** @var SiteSurveyQuestionOption $questionOption */
        $questionOption = factory(SiteSurveyQuestionOption::class)->create();

        self::expectException(NotAllowedException::class);
        $this->service->attachQuestion($job->id, $question->id, $questionOption->id);
    }

    public function testAttachQuestionWithAnswerToJob()
    {
        $job = $this->fakeJobWithStatus();
        /** @var SiteSurveyQuestion $question */
        $question = factory(SiteSurveyQuestion::class)->create([
            'is_active' => true,
        ]);

        $this->service->attachQuestion($job->id, $question->id, null, 'Yes');

        JobSiteSurveyQuestion::query()->where([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $question->id,
        ])->firstOrFail();

        self::assertEquals(1, $job->siteSurveyQuestions()->count());
    }

    public function testFailToAttachQuestionToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var SiteSurveyQuestion $question */
        $question = factory(SiteSurveyQuestion::class)->create();

        self::expectException(NotAllowedException::class);
        $this->service->attachQuestion($job->id, $question->id);
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

        $this->service->detachQuestion($job->id, $question->id);

        self::expectException(ModelNotFoundException::class);
        JobSiteSurveyQuestion::query()->where([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $question->id,
        ])->firstOrFail();
    }

    public function testFailToDetachQuestionFromClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var SiteSurveyQuestion $question */
        $question = factory(SiteSurveyQuestion::class)->create();
        JobSiteSurveyQuestion::insert([
            'job_id'                  => $job->id,
            'site_survey_question_id' => $question->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->detachQuestion($job->id, $question->id);
    }
}
