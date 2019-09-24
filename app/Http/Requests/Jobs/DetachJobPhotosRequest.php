<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class DetachJobPhotosRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="photo_ids",
 *          description="Photo identifiers",
 *          type="array",
 *          @OA\Items(type="integer")
 *     )
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class DetachJobPhotosRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'photo_ids'   => 'required|array',
            'photo_ids.*' => 'integer',
        ];
    }

    /**
     * @return array
     */
    public function getPhotoIds(): array
    {
        return $this->get('photo_ids', []);
    }
}
