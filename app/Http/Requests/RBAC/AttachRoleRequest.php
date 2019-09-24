<?php

namespace App\Http\Requests\RBAC;

use App\Http\Requests\ApiRequest;

/**
 * Class AttachRoleRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"roles"},
 *     @OA\Property(
 *          property="roles",
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
class AttachRoleRequest extends ApiRequest
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
            'roles'   => 'required|array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    /**
     * Returns list of role ids from request.
     *
     * @return array
     */
    public function getRoleIds(): array
    {
        return $this->input('roles');
    }
}
