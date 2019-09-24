<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;

/**
 * Class GetJobNotesTemplatesRequest
 *
 * @package App\Http\Requests\Jobs
 */
class GetJobNotesTemplatesRequest extends ApiRequest
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
            'active' => 'in:true,false',
        ];
    }
}
