<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;

/**
 * Class GetAddressesRequest
 *
 * @package App\Http\Requests\Addresses
 */
class GetAddressesRequest extends ApiRequest
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
            'suburb_id'    => 'exists:suburbs,id',
            'state_id'     => 'exists:states,id',
            'address_line' => 'string',
            'contact_name' => 'string',
        ];
    }
}
