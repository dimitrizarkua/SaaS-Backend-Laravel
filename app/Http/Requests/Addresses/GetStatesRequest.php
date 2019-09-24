<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;

/**
 * Class GetStatesRequest
 *
 * @package App\Http\Requests\Addresses
 */
class GetStatesRequest extends ApiRequest
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
            'country_id' => 'integer',
        ];
    }
}
