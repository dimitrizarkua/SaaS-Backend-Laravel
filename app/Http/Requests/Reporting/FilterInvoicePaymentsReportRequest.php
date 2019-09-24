<?php

namespace App\Http\Requests\Reporting;

use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter;
use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use Illuminate\Validation\Rule;

/**
 * Class FilterInvoicePaymentsReportRequest
 *
 * @package App\Http\Requests\Finance
 */
class FilterInvoicePaymentsReportRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'location_id'          => [
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'recipient_contact_id' => 'integer|exists:contacts,id',
            'date_from'            => 'string|date_format:Y-m-d',
            'date_to'              => 'string|date_format:Y-m-d',
            'type'                 => ['string', Rule::in(PaymentTypes::values())],
            'amount_from'          => 'numeric',
            'amount_to'            => 'numeric',
            'user_id'              => 'integer|exists:users,id',
        ];
    }

    /**
     * @return \App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter
     *
     * @throws \JsonMapper_Exception
     */
    public function getInvoicePaymentsReportFilter(): InvoicePaymentsReportFilter
    {
        $filter = new InvoicePaymentsReportFilter($this->validated());
        if (null === $this->getUserId()) {
            $filter->user_id = $this->user()->id;
        }

        return $filter;
    }

    /**
     * Returns user_id given in request.
     *
     * @return null|int
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id', null);
    }
}
