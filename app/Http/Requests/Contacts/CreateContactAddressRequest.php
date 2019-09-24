<?php

namespace App\Http\Requests\Contacts;

use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class CreateContactAddressRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"type"},
 *     @OA\Property(
 *          property="type",
 *          description="Address type",
 *          type="string",
 *          example="billing",
 *          enum={"mailing","street"},
 *     ),
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreateContactAddressRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(AddressContactTypes::values())],
        ];
    }
}
