<?php

namespace App\Http\Requests\Jobs;

use OpenApi\Annotations as OA;

/**
 * Class UpdateJobPhotoRequest
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/AttachJobPhotoRequest")
 *     },
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class UpdateJobPhotoRequest extends AttachJobPhotoRequest
{
}
