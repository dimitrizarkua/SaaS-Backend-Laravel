<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Photos\UpdatePhotoRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateAvatarRequest
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/UpdatePhotoRequest")},
 * )
 *
 * @package App\Http\Requests\Users
 */
class UpdateAvatarRequest extends UpdatePhotoRequest
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
