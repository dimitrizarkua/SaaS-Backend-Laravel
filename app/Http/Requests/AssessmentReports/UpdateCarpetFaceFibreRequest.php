<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateCarpetFaceFibreRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Carpet face fibre name",
 *         type="string",
 *         example="Polyester",
 *     ),
 * )
 */
class UpdateCarpetFaceFibreRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|unique:carpet_face_fibres',
        ];
    }
}
