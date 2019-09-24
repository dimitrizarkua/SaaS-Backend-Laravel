<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class AttachJobPhotoRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="description",
 *          description="Photo description.",
 *          type="string",
 *          default="Some text",
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class AttachJobPhotoRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'description' => 'string|nullable',
        ];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->get('description', '') ?? '';
    }
}
