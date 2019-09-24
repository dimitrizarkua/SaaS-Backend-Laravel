<?php

namespace App\Http\Requests\Locations;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateLocationRequest
 *
 * @OA\Schema(
 *     type="object",
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
class UpdateLocationRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'code'      => Rule::unique('locations'),
            'name'      => Rule::unique('locations'),
            'tz_offset' => 'integer',
        ];
    }
}
