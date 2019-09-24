<?php

namespace App\Http\Requests\Photos;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations\Items;

/**
 * Class DownloadMultiplePhotosRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"photo_ids"},
 *     @OA\Property(
 *          property="photo_ids",
 *          type="array",
 *          description="Photo identifiers",
 *          @Items(type="integer",example=1)
 *     ),
 * )
 *
 * @package App\Http\Requests\Photos
 */
class DownloadMultiplePhotosRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'photo_ids.*' => 'int',
        ];
    }

    /**
     * @return array
     */
    public function getPhotoIds(): array
    {
        return $this->get('photo_ids');
    }
}
