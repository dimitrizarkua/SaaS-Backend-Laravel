<?php

namespace App\Http\Requests\RBAC;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateRoleRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          example="admin",
 *     ),
 *     @OA\Property(
 *          property="display_name",
 *          description="Display name",
 *          type="string",
 *          example="Admin"
 *      ),
 *     @OA\Property(
 *          property="description",
 *          description="Description",
 *          type="string",
 *          example="Allows to manage many internal resources"
 *      ),
 * )
 *
 * @package App\Http\Requests\RBAC
 */
class CreateRoleRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'         => 'required|unique:roles',
            'display_name' => 'string|nullable',
            'description'  => 'string|nullable',
        ];
    }
}
