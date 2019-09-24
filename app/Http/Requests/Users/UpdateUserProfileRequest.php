<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\ApiRequest;
use App\Rules\FirstName;
use App\Rules\LastName;

/**
 * Class UpdateUserProfileRequest
 *
 * @package App\Http\Requests\Users
 *
 * @OA\Schema(
 *     type="object",
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
 *     )
 * )
 */
class UpdateUserProfileRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => [new FirstName()],
            'last_name'  => [new LastName()],
        ];
    }

    /**
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->get('first_name');
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->get('last_name');
    }
}
