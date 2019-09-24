<?php

namespace App\Http\Requests\RBAC;

use App\Rules\FirstName;
use App\Rules\LastName;
use App\Rules\Password;
use App\Http\Requests\ApiRequest;

/**
 * Class CreateUserRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"email","first_name","last_name","password"},
 *     @OA\Property(
 *         property="email",
 *         description="Email",
 *         type="string",
 *         example="john.smith@gmail.com",
 *     ),
 *     @OA\Property(
 *         property="password",
 *         description="Password, 8 to 50 characters long.",
 *         type="string",
 *         format="password",
 *         minLength=8,
 *         maxLength=50,
 *         example="SomeStrongPassword1!"
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         description="First name",
 *         type="string",
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         description="Last name",
 *         type="string",
 *         example="Smith"
 *     ),
 * )
 *
 * @package App\Http\Requests\RBAC
 */
class CreateUserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'      => 'required|email|unique:users',
            'first_name' => ['required', new FirstName()],
            'last_name'  => ['required', new LastName()],
            'password'   => ['required', new Password()],
        ];
    }
}
