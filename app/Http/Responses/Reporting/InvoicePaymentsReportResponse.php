<?php

namespace App\Http\Responses\Reporting;

use App\Components\Reporting\Resources\InvoicePaymentsReportResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicePaymentsReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class InvoicePaymentsReportResponse extends ApiOKResponse
{
    protected $resource = InvoicePaymentsReportResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/InvoicePaymentsReportResource"
     * ),
     * @var \App\Components\Reporting\Resources\InvoicePaymentsReportResource[]
     */
    protected $data;
}
