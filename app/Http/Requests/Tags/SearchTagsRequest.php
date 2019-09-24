<?php

namespace App\Http\Requests\Tags;

use App\Components\Tags\Enums\TagTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class SearchTagsRequest
 *
 * @OA\Schema(
 *     type="object",
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
 *          property="count",
 *          description="Tags count",
 *          type="integer",
 *          example=10
 *      ),
 * )
 *
 * @package App\Http\Requests\Tags
 */
class SearchTagsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'  => 'string',
            'type'  => ['string', Rule::in(TagTypes::values())],
            'count' => 'integer',
        ];
    }
}
