<?php

namespace App\Http\Requests\Contacts;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class CreateContactCategoryRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name","type"},
 *     @OA\Property(
 *          property="name",
 *          description="Contact category name",
 *          type="string",
 *          example="Customer",
 *     ),
 *     @OA\Property(
 *          property="type",
 *          ref="#/components/schemas/ContactCategoryTypes"
 *     ),
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreateContactCategoryRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:contact_categories',
            'type' => ['required','string', Rule::in(ContactCategoryTypes::values())],
        ];
    }
}
