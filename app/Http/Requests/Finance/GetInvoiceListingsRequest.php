<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Models\Filters\InvoiceListingFilter;
use App\Http\Requests\ApiRequest;

/**
 * Class GetInvoiceListingsRequest
 *
 * @package App\Http\Requests\Finance
 */
class GetInvoiceListingsRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'locations'            => 'array',
            'locations.*'          => 'integer|exists:locations,id',
            'recipient_contact_id' => 'integer|exists:contacts,id',
            'job_id'               => 'integer|exists:jobs,id',
            'due_date_from'        => 'string|date_format:Y-m-d',
            'due_date_to'          => 'string|date_format:Y-m-d',
        ];
    }

    /**
     * @return \App\Components\Finance\Models\Filters\InvoiceListingFilter
     * @throws \JsonMapper_Exception
     */
    public function getInvoiceListingFilter(): InvoiceListingFilter
    {
        $filter          = new InvoiceListingFilter($this->validated());
        $filter->user_id = $this->user()->id;

        return $filter;
    }
}
