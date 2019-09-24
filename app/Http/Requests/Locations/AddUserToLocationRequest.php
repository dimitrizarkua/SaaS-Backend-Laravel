<?php

namespace App\Http\Requests\Locations;

use App\Http\Requests\ApiRequest;

/**
 * Class AddUserToLocationRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="primary",
 *          description="Defines if this location should be primary for user or not.",
 *          type="boolean",
 *          example=false
 *      ),
 * )
 *
 * @package App\Http\Requests\Locations
 */
class AddUserToLocationRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'primary' => 'boolean',
        ];
    }
}
