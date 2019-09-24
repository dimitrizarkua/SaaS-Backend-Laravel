<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;

/**
 * Class SearchJobsRequest
 *
 * @package App\Http\Requests\Jobs
 */
class SearchJobsRequest extends ApiRequest
{
    protected $booleanFields = [
        'include_closed',
    ];

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
            'id'             => 'required|string',
            'per_page'       => 'integer',
            'include_closed' => 'nullable|boolean',
        ];
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'id' => $this->get('id'),
        ];
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->get('per_page', 10);
    }

    /**
     * @return bool|null
     */
    public function getIncludeClosed(): ?bool
    {
        return $this->get('include_closed', false);
    }
}
