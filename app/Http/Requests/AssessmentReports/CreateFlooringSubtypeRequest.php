<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateFlooringSubtypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"flooring_type_id", "name"},
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
class CreateFlooringSubtypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'flooring_type_id' => 'required|integer|exists:flooring_types,id',
            'name'             => 'required|string|unique:flooring_subtypes',
        ];
    }
}
