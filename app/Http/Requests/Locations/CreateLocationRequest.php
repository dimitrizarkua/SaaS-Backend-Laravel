<?php

namespace App\Http\Requests\Locations;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateLocationRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"code","name"},
 *     @OA\Property(
 *          property="code",
 *          description="Location type",
 *          type="string",
 *          example="SYD",
 *     ),
 *     @OA\Property(
 *          property="name",
 *          description="Location name",
 *          type="string",
 *          example="Sydney"
 *      ),
 *     @OA\Property(
 *          property="tz_offset",
 *          description="Timezone offset in minutes.",
 *          type="integer",
 *          example=120,
 *      ),
 * )
 *
 * @package App\Http\Requests\Locations
 */
class CreateLocationRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'code'      => ['required', Rule::unique('locations')],
            'name'      => ['required', Rule::unique('locations')],
            'tz_offset' => 'integer',
        ];
    }
}
