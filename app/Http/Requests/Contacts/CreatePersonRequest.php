<?php

namespace App\Http\Requests\Contacts;

use App\Rules\FirstName;
use App\Rules\LastName;
use OpenApi\Annotations as OA;

/**
 * Class CreatePersonRequest
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/CreateContactRequest")
 *     },
 *     required={"first_name","last_name"},
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreatePersonRequest extends CreateContactRequest
{
    /**
     * @OA\Property(
     *     property="first_name",
     *     description="First name",
     *     type="string",
     *     example="John",
     * ),
     * @OA\Property(
     *     property="last_name",
     *     description="Last name",
     *     type="string",
     *     example="Smith",
     * ),
     * @OA\Property(
     *     property="job_title",
     *     description="Job title",
     *     type="string",
     *     example="Technician",
     * ),
     * @OA\Property(
     *     property="direct_phone",
     *     description="Direct phone",
     *     type="string",
     *     example="0398776000",
     * ),
     * @OA\Property(
     *     property="mobile_phone",
     *     description="Mobile phone",
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
        return array_merge(
            parent::rules(),
            [
                'first_name'   => ['required', new FirstName()],
                'last_name'    => ['required', new LastName()],
                'job_title'    => 'nullable|string',
                'direct_phone' => 'required_without_all:business_phone,mobile_phone|nullable|string',
                'mobile_phone' => 'required_without_all:business_phone,direct_phone|nullable|string',
            ]
        );
    }
}
