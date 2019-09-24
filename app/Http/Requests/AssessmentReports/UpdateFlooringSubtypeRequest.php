<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateFlooringSubtypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="flooring_type_id",
 *         description="Flooring type identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Flooring subtype name",
 *         type="string",
 *         example="Laminate",
 *     ),
 * )
 */
class UpdateFlooringSubtypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'flooring_type_id' => 'integer|exists:flooring_types,id',
            'name'             => 'string|unique:flooring_subtypes',
        ];
    }
}
