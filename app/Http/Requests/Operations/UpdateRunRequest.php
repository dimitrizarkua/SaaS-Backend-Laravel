<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRunRequest
 *
 * @OA\Schema(
 *     type="object",
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
class UpdateRunRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }
}
