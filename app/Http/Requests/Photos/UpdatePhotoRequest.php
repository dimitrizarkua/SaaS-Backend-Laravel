<?php

namespace App\Http\Requests\Photos;

/**
 * Class UpdatePhotoRequest
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/CreatePhotoRequest")},
 * )
 *
 * @package App\Http\Requests\Photos
 */
class UpdatePhotoRequest extends CreatePhotoRequest
{
}
