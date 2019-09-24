<?php

namespace App\Http\Requests\Reporting;

use App\Components\Reporting\Models\Filters\GSTReportFilter;
use App\Http\Requests\ApiRequest;
use App\Models\Filter;

/**
 * Class GSTReportRequest
 *
 * @package App\Http\Requests\Reporting
 */
class GSTReportRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|integer|exists:locations,id',
            'date_from'   => 'required|string|date_format:Y-m-d',
            'date_to'     => 'required|string|date_format:Y-m-d',
        ];
    }

    /**
     * @throws \JsonMapper_Exception
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return new GSTReportFilter($this->validated());
    }
}
