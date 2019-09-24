<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateRunTemplateRunRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
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
class CreateRunTemplateRunRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }
}
