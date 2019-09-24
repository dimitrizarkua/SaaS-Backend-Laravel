<?php

namespace App\Http\Requests\Tags;

use App\Components\Tags\Enums\TagTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateTagRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"type","name","is_alert","color"},
 *     @OA\Property(
 *          property="type",
 *          description="Tag type",
 *          type="string",
 *          example="Job",
 *     ),
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
class CreateTagRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'     => 'required|string|unique_with:tags,type',
            'type'     => ['required', 'string', Rule::in(TagTypes::values())],
            'is_alert' => 'required|boolean',
            'color'    => 'required|integer',
        ];
    }
}
