<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ListTasksRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class ListTasksRequest extends ApiRequest
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
        ];
    }

    /**
     * @return integer
     */
    public function getLocationId(): int
    {
        return $this->get('location_id');
    }
}
