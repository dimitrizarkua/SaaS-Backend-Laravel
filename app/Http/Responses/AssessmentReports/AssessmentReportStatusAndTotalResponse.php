<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportStatusAndTotalResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportStatusAndTotalResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="AssessmentReportStatusAndTotal",
     *     type="object",
     *     @OA\Property(
     *         property="status",
     *         description="Last status of assessment report",
     *         type="string",
     *         example="draft",
     *     ),
     *     @OA\Property(
     *         property="total",
     *         description="Total amount of assessment report",
     *         type="number",
     *         format="float",
     *         example=840.50,
     *     ),
     * ),
     *
     * @OA\Property(
     *     property="data",
     *     ref="#/components/schemas/AssessmentReportStatusAndTotal",
     *     description="Last status and total amount of assessment report",
     * ),
     */
    protected $data;
}
