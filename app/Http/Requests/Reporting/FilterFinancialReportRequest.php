<?php

namespace App\Http\Requests\Reporting;

use App\Components\Reporting\Models\Filters\FinancialReportFilterData;
use App\Http\Requests\ApiRequest;

/**
 * Class FilterFinancialReportRequest
 *
 * @package App\Http\Requests\Finance
 */
class FilterFinancialReportRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'location_id'        => 'required|integer|exists:locations,id',
            'gl_account_id'      => 'integer|exists:gl_accounts,id',
            'tag_ids'            => 'array',
            'tag_ids.*'          => 'integer|exists:tags,id',
            'current_date_from'  => 'required|string|date_format:Y-m-d',
            'current_date_to'    => 'required|string|date_format:Y-m-d',
            'previous_date_from' => 'required|string|date_format:Y-m-d',
            'previous_date_to'   => 'required|string|date_format:Y-m-d',
        ];
    }

    /**
     * @return \App\Components\Reporting\Models\Filters\FinancialReportFilterData
     *
     * @throws \JsonMapper_Exception
     */
    public function getFinancialReportFilter(): FinancialReportFilterData
    {
        $filter = new FinancialReportFilterData($this->validated());

        return $filter;
    }
}
