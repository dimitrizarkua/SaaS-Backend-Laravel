<?php

namespace App\Http\Requests\Contacts;

use App\Components\Contacts\Models\Enums\ContactStatuses;
use Illuminate\Validation\Rule;

/**
 * Class GetContactsRequest
 *
 * @package App\Http\Requests\Contacts
 */
class GetContactsRequest extends SearchContactsRequest
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
        return array_merge(
            parent::rules(),
            [
                'contact_status' => ['string', Rule::in(ContactStatuses::values())],
                'active_in_days' => 'integer',
            ]
        );
    }
}
