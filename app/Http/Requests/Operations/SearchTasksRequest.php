<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SearchTasksRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id","term"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="term",
 *          type="string",
 *          description="Search term",
 *          example="Task xxx-112233"
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class SearchTasksRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|integer',
            'term'        => 'required|string',
        ];
    }
}
