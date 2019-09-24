<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateFlooringTypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Flooring type name",
 *         type="string",
 *         example="Carpet",
 *     ),
 * )
 */
class UpdateFlooringTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|unique:flooring_types',
        ];
    }
}
