<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobCostingCountersResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobCostingCountersResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="CostingCounters",
     *     type="object",
     *     @OA\Property(
     *         property="materials",
     *         description="Counter for page job materials",
     *         type="integer",
     *         example=4,
     *     ),
     *     @OA\Property(
     *         property="equipment",
     *         description="Counter for page job equipment",
     *         type="integer",
     *         example=2,
     *     ),
     *     @OA\Property(
     *         property="time",
     *         description="Counter for page job time",
     *         type="integer",
     *         example=2,
     *     ),
     *     @OA\Property(
     *         property="purchase_orders",
     *         description="Counter for page PO's, Storage",
     *         type="integer",
     *         example=3,
     *     ),
     * ),
     *
     * @OA\Property(
     *     property="data",
     *     ref="#/components/schemas/CostingCounters",
     *     description="Costing counters for specific job",
     * ),
     */
    protected $data;
}
