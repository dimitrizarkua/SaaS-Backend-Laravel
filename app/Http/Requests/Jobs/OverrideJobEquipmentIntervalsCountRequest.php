<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class OverrideJobEquipmentIntervalsCountRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"intervals_count_override"},
 *     @OA\Property(
 *         property="intervals_count_override",
 *         description="Override count of intervals",
 *         type="integer",
 *         example=3,
 *     ),
 * )
 */
class OverrideJobEquipmentIntervalsCountRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'intervals_count_override' => 'required|integer|min:1',
        ];
    }

    /**
     * Returns intervals count override.
     *
     * @return int
     */
    public function getIntervalsCountOverride(): int
    {
        return $this->get('intervals_count_override');
    }
}
