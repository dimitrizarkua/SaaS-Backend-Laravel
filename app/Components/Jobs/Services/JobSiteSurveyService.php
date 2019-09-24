<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobSiteSurveyServiceInterface;
use App\Components\SiteSurvey\SiteSurvey;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use Illuminate\Support\Collection;

/**
 * Class JobSiteSurveyService
 *
 * @package App\Components\Jobs\Services
 */
class JobSiteSurveyService extends JobsEntityService implements JobSiteSurveyServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function getSiteSurvey(int $jobId): SiteSurvey
    {
        $siteSurvey = new SiteSurvey($jobId);

        return $siteSurvey;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function attachQuestion(
        int $jobId,
        int $questionId,
        ?int $questionOptionId = null,
        ?string $answer = null
    ): void {
        $job = $this->jobsService()
            ->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('No changes can be made to closed or cancelled job.');
        }

        if (null !== $questionOptionId) {
            $questionOptionIds = $this->getQuestionOptionsId($questionId);
            if (!$questionOptionIds->contains($questionOptionId)) {
                throw new NotAllowedException('No such answer option exist for specified question.');
            }
            $attributes['site_survey_question_option_id'] = $questionOptionId;
            $attributes['answer']                         = null;
        } else {
            $attributes['site_survey_question_option_id'] = null;
            $attributes['answer']                         = $answer;
        }

        $existingJobSiteSurveyQuestion = $job->siteSurveyQuestions()
            ->wherePivot('site_survey_question_id', '=', $questionId)
            ->first();

        if ($existingJobSiteSurveyQuestion) {
            $job->siteSurveyQuestions()
                ->updateExistingPivot($questionId, $attributes);
        } else {
            $job->siteSurveyQuestions()
                ->attach($questionId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function detachQuestion(int $jobId, int $questionId): void
    {
        $job = $this->jobsService()
            ->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('No changes can be made to closed or cancelled job.');
        }

        $job->siteSurveyQuestions()->detach($questionId);
    }

    /**
     * Returns options ids of provided question.
     *
     * @param int $questionId
     *
     * @return Collection
     */
    private function getQuestionOptionsId(int $questionId): Collection
    {
        return SiteSurveyQuestion::with(['siteSurveyQuestionOptions'])
            ->findOrFail($questionId)
            ->siteSurveyQuestionOptions
            ->pluck('id');
    }
}
