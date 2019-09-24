<?php

namespace App\Http\Responses\Reporting;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactVolumeReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class ContactVolumeReportResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="object",
     *     ref="#/components/schemas/ContactVolumeReportData"
     *)
     */
    protected $data;
}
