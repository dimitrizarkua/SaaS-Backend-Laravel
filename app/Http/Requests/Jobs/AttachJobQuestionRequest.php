<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class AttachJobQuestionRequest
 *
 * @package App\Http\Requests\Jobs
 *
 * @OA\Schema(
 *     type="object",
 *     anyOf={
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="site_survey_question_option_id",
 *                 description="Id of attached site survey question option.",
 *                 type="int",
 *                 default="1",
 *             ),
 *         ),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="answer",
 *                 description="Answer on a question",
 *                 type="string",
 *                 default="Probably",
 *             ),
 *         )
 *     }
 * )
 */
class AttachJobQuestionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'site_survey_question_option_id' => [
                'integer',
                'required_without:answer',
                Rule::exists('site_survey_question_options', 'id'),
            ],
            'answer'                         => 'string|required_without:site_survey_question_option_id',
        ];
    }

    /**
     * Returns id of site survey question option.
     *
     * @return int|null
     */
    public function getQuestionOptionId(): ?int
    {
        return $this->get('site_survey_question_option_id', null);
    }

    /**
     * Returns answer for site survey question.
     *
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->get('answer', null);
    }
}
