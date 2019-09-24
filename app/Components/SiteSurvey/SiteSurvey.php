<?php

namespace App\Components\SiteSurvey;

use App\Components\Jobs\Models\Job;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;

/**
 * Class SiteSurvey
 *
 * @package App\Components\SiteSurvey
 *
 * @property Job   $job
 * @property array $allQuestions
 * @property array $jobQuestions
 * @property array $jobRooms
 */
class SiteSurvey
{
    /**
     * List of all survey site questions with options.
     *
     * @var array|\Illuminate\Support\Collection
     */
    public $allQuestions = [];

    /**
     * List of job questions with answers.
     *
     * @var array|\Illuminate\Support\Collection
     */
    public $jobQuestions = [];

    /**
     * List of job rooms.
     *
     * @var array|\Illuminate\Support\Collection
     */
    public $jobRooms = [];

    /**
     * Specific job for which the site survey is construct.
     *
     * @var Job
     */
    private $job;

    /**
     * SiteSurvey constructor.
     *
     * @param int $jobId
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function __construct(int $jobId)
    {
        $this->job          = Job::findOrFail($jobId);
        $this->allQuestions = $this->getAllQuestions();
        $this->jobQuestions = $this->getJobQuestions();
        $this->jobRooms     = $this->getJobRooms();
        unset($this->job);
    }

    /**
     * Returns list of all site survey questions with its options.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAllQuestions()
    {
        return SiteSurveyQuestion::active()
            ->with('siteSurveyQuestionOptions')
            ->get();
    }

    /**
     * Returns list of specific job questions with answers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getJobQuestions()
    {
        return $this->job
            ->siteSurveyQuestions()
            ->get();
    }

    /**
     * Returns list of specific job rooms.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getJobRooms()
    {
        return $this->job
            ->jobRooms()
            ->get();
    }
}
