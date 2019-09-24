<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;

/**
 * Class JobLabourTotalAmountResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobLabourTotalAmountResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="total_amount",
     *     type="number",
     *     description="Total amount of job labours assigned to job with insurer contract restrictions",
     *     example="3.33",
     * ),
     */
    protected $data;
}
