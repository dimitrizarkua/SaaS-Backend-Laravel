<?php

namespace App\Http\Requests\Contacts;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateContactCategoryRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *          property="name",
 *          description="Contact category name",
 *          type="string",
 *          example="Customer",
 *     ),
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class UpdateContactCategoryRequest extends ApiRequest
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
        ];
    }
}
