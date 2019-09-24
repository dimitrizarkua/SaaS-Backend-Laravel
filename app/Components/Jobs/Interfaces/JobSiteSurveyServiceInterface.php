<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\SiteSurvey\SiteSurvey;

/**
 * Interface JobSiteSurveyServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobSiteSurveyServiceInterface
{
    /**
     * Returns site survey for specific job.
     *
     * @param int $jobId Id of a job.
     *
     * @return SiteSurvey
     */
    public function getSiteSurvey(int $jobId): SiteSurvey;

    /**
     * Attaches question to a job.
     *
     * @param int         $jobId            Id of job.
     * @param int         $questionId       Id of question.
     * @param null|int    $questionOptionId Id of question option which attached to the job.
     * @param string|null $answer           Answer for question.
     */
    public function attachQuestion(
        int $jobId,
        int $questionId,
        ?int $questionOptionId = null,
        ?string $answer = null
    ): void;

    /**
     * Detaches question from a job.
     *
     * @param int $jobId      Id of job.
     * @param int $questionId Id of question which detached from the job.
     */
    public function detachQuestion(int $jobId, int $questionId): void;
}
