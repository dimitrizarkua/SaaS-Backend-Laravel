<?php

namespace App\Http\Requests\Search;

use App\Http\Requests\ApiRequest;

/**
 * Class UserAndTeamsSearchRequest
 *
 * @package App\Http\Requests\Search
 */
class UserAndTeamsSearchRequest extends ApiRequest
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
            'term' => 'required|string',
        ];
    }

    /**
     * @return string
     */
    public function getTerm(): string
    {
        return $this->input('term');
    }
}
