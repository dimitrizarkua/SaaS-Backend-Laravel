<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateCarpetFaceFibreRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Carpet face fibre name",
 *         type="string",
 *         example="Polyester",
 *     ),
 * )
 */
class CreateCarpetFaceFibreRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:carpet_face_fibres',
        ];
    }
}
