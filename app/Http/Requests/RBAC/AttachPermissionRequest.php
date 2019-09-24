<?php

namespace App\Http\Requests\RBAC;

use App\Http\Requests\ApiRequest;
use App\Rules\PermissionExists;

/**
 * Class AttachPermissionRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"permissions"},
 *     @OA\Property(
 *          property="permissions",
 *          type="array",
 *          @OA\Items(
 *              type="integer",
 *              description="role id",
 *              example=1
 *          ),
 *     ),
 * )
 *
 * @package App\Http\Requests\RBAC
 */
class AttachPermissionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'permissions'   => 'required|array',
            'permissions.*' => [new PermissionExists()],
        ];
    }

    /**
     * Returns list of permission names from request.
     *
     * @return string[]
     */
    public function getPermissionNames(): array
    {
        return $this->input('permissions');
    }
}
