<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateRunTemplateRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Name",
 *          example="Run 1"
 *     ),
 * )
 *
 * @package App\Http\Requests\Operations
 */
class CreateRunTemplateRequest extends ApiRequest
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
            'name'        => 'nullable|string',
        ];
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->get('location_id');
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }
}
