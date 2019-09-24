<?php

namespace App\Http\Requests\Tags;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateTagRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="name",
 *          description="Tag name",
 *          type="string",
 *          example="Urgent"
 *      ),
 *     @OA\Property(
 *          property="is_alert",
 *          description="Is alert tag",
 *          type="boolean",
 *          example=true
 *      ),
 *     @OA\Property(
 *          property="color",
 *          description="Tag color",
 *          type="integer",
 *          example=16777215
 *      ),
 * )
 *
 * @package App\Http\Requests\Tags
 */
class UpdateTagRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'     => 'string|unique_with:tags,type',
            'is_alert' => 'boolean',
            'color'    => 'integer',
        ];
    }
}
