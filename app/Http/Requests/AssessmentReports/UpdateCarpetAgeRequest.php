<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateCarpetAgeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Carpet age name",
 *         type="string",
 *         example="1-3",
 *     ),
 * )
 */
class UpdateCarpetAgeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|unique:carpet_ages',
        ];
    }
}
