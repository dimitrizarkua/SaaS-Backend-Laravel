<?php

namespace App\Http\Requests\Contacts;

use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateContactStatusRequest
 *
 * @package App\Http\Requests\Contacts
 * @OA\Schema(
 *     type="object",
 *     required={"status"},
 *     @OA\Property(
 *         property="status",
 *         description="New status of the contact",
 *         allOf={@OA\Schema(ref="#/components/schemas/ContactStatuses")}
 *     ),
 * )
 */
class UpdateContactStatusRequest extends ApiRequest
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
            'status' => ['required', 'string', Rule::in(ContactStatuses::values())],
        ];
    }

    /**
     * Return status value from request.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->get('status');
    }
}
