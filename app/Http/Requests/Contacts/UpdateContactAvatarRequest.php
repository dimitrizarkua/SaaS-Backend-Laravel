<?php

namespace App\Http\Requests\Contacts;

use App\Http\Requests\Photos\UpdatePhotoRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateContactAvatarRequest
 *
 * @package App\Http\Requests\Contacts
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/UpdatePhotoRequest")},
 * )
 */
class UpdateContactAvatarRequest extends UpdatePhotoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png|dimensions:max_width=300,max_height=300',
        ];
    }
}
