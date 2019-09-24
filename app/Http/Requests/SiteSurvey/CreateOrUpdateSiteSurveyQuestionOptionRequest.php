<?php

namespace App\Http\Requests\SiteSurvey;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateOrUpdateSiteSurveyQuestionOptionRequest
 *
 * @package App\Http\Requests\SiteSurvey
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="name",
 *          description="Site survey question option name",
 *          type="string",
 *          example="Yes"
 *      ),
 * )
 */
class CreateOrUpdateSiteSurveyQuestionOptionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }
}
