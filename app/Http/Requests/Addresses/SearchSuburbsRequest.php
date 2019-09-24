<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class SearchSuburbsRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="state_id",
 *          description="State type",
 *          type="integer",
 *          example=1,
 *     ),
 *     @OA\Property(
 *          property="term",
 *          description="Suburb name",
 *          type="string",
 *          example="Williamstown"
 *      ),
 *     @OA\Property(
 *          property="count",
 *          description="Defines maximum number of items in result set",
 *          type="integer",
 *          example=10
 *      ),
 * )
 *
 * @package App\Http\Requests\Addresses
 */
class SearchSuburbsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'term'     => 'required|string',
            'state_id' => ['integer', Rule::exists('states', 'id')],
            'count'    => 'integer',
        ];
    }
}
