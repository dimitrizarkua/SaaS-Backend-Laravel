<?php

namespace App\Http\Requests\Contacts;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchContactsRequest
 *
 * @package App\Http\Requests\Contacts
 */
class SearchContactsRequest extends ApiRequest
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
            'contact_category_id'   => 'integer',
            'contact_category_type' => ['string', Rule::in(ContactCategoryTypes::values())],
            'contact_type'          => ['string', Rule::in(ContactTypes::values())],
            'term'                  => 'string',
        ];
    }
}
