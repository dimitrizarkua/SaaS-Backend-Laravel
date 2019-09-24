<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ForgotPasswordRequest
 *
 * @package App\Http\Requests\Auth
 *
 * @OA\Schema(
 *     type="object",
 *     required={"email"},
 *     @OA\Property(
 *         property="email",
 *         description="Users email",
 *         type="string",
 *         example="john.smith@gmail.com"
 *     ),
 * )
 */
class ForgotPasswordRequest extends ApiRequest
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
            'email' => 'required|email',
        ];
    }

    public function getEmail(): string
    {
        return $this->input('email');
    }
}
