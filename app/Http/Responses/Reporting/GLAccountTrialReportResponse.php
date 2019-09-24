<?php

namespace App\Http\Responses\Reporting;

use App\Components\Reporting\Resources\GLAccountTrialReportResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountTrialReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class GLAccountTrialReportResponse extends ApiOKResponse
{
    protected $resource = GLAccountTrialReportResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/GLAccountTrialReportResource"
     * ),
     * @var \App\Components\Reporting\Resources\GLAccountTrialReportResource
     */
    protected $data;
}
