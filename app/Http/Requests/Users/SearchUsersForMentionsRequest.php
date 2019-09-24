<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\ApiRequest;

/**
 * Class SearchUsersForMentionsRequest
 *
 * @package App\Http\Requests\Users
 */
class SearchUsersForMentionsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'  => 'string|required',
        ];
    }
}
