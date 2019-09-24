<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateCarpetConstructionTypeRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Carpet construction type name",
 *         type="string",
 *         example="Cut & Uncut Patterned Carpet",
 *     ),
 * )
 */
class CreateCarpetConstructionTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:carpet_construction_types',
        ];
    }
}
