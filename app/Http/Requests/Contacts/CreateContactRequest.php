<?php

namespace App\Http\Requests\Contacts;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateContactRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"contact_category_id"},
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreateContactRequest extends ApiRequest
{
    /**
     * @OA\Property(
     *     property="contact_category_id",
     *     description="Contact category id",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="email",
     *     description="Email",
     *     type="string",
     *     example="john.smith@gmail.com",
     * ),
     * @OA\Property(
     *     property="business_phone",
     *     description="Business phone",
     *     type="string",
     *     example="0398776000",
     * ),
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'contact_category_id' => 'required|integer|exists:contact_categories,id',
            'email'               => 'nullable|email',
            'business_phone'      => 'nullable|string',
        ];
    }
}
