<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateCarpetConstructionTypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Carpet construction type name",
 *         type="string",
 *         example="Cut & Uncut Patterned Carpet",
 *     ),
 * )
 */
class UpdateCarpetConstructionTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|unique:carpet_construction_types',
        ];
    }
}
