<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SetPasswordRequest
 *
 * @package App\Http\Requests\Auth
 *
 * @OA\Schema(
 *     type="object",
 *     required={"token","password"},
 *     @OA\Property(
 *         property="token",
 *         description="Reset password token",
 *         type="string",
 *         example="TOKEN"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         description="New password",
 *         type="string",
 *         example="password"
 *     ),
 * )
 *
 * @property string $token
 * @property string $password
 */
class SetPasswordRequest extends ApiRequest
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
            'token'    => 'required|string',
            'password' => 'required|string',
        ];
    }
}
