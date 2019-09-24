<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;

/**
 * Class JobMaterialsTotalAmountResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobMaterialsTotalAmountResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="JobMaterialsTotal",
     *     type="object",
     *     @OA\Property(
     *          property="total_amount",
     *          type="number",
     *          description="Total amount of job materials assigned to job",
     *          example="3",
     *     ),
     *     @OA\Property(
     *          property="total_amount_override",
     *          type="number",
     *          description="Overridden total amount of job materials assigned to job",
     *          example="3",
     *     ),
     * ),
     */
    protected $data;
}
