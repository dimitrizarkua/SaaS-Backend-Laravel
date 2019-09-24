<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateFlooringTypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Flooring type name",
 *         type="string",
 *         example="Carpet",
 *     ),
 * )
 */
class CreateFlooringTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:flooring_types',
        ];
    }
}
