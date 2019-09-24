<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Models\Filters\CreditNoteListingFilter;
use App\Http\Requests\ApiRequest;

/**
 * Class GetCreditNoteRequest
 *
 * @package App\Http\Requests\Finance
 *
 */
class GetCreditNoteRequest extends ApiRequest
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
            'locations'            => 'array',
            'locations.*'          => 'integer|exists:locations,id',
            'recipient_contact_id' => 'integer|exists:contacts,id',
            'date_from'            => 'string|date_format:Y-m-d',
            'date_to'              => 'string|date_format:Y-m-d',
            'job_id'               => 'integer|exists:jobs,id',
        ];
    }

    /**
     * @return \App\Components\Finance\Models\Filters\CreditNoteListingFilter
     * @throws \JsonMapper_Exception
     */
    public function getCreditNoteListingFilter(): CreditNoteListingFilter
    {
        $filter          = new CreditNoteListingFilter($this->validated());
        $filter->user_id = $this->user()->id;

        return $filter;
    }
}
