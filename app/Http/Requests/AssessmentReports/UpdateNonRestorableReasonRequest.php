<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateNonRestorableReasonRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Non restorable reason name",
 *         type="string",
 *         example="Burned out",
 *     ),
 * )
 */
class UpdateNonRestorableReasonRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string|unique:non_restorable_reasons',
        ];
    }
}
