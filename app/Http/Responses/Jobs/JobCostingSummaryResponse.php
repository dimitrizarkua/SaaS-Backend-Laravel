<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobCostingSummaryResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobCostingSummaryResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="CostingSummary",
     *     type="object",
     *     @OA\Property(
     *          property="total_costed",
     *          description="Sum of total amounts the actual usage (non-fudged) figures",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="remaining",
     *          description="Difference between job's budget and used items",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="gross_profit",
     *          description="Gross profit of job in percent",
     *          type="number",
     *          format="float",
     *          example="12",
     *     ),
     *     @OA\Property(
     *          property="labour_used",
     *          description="Total amount of used labours for job",
     *          type="number",
     *          format="float",
     *          example="12",
     *     ),
     *     @OA\Property(
     *          property="equipment_used",
     *          description="Total amount of used equipment for job",
     *          type="number",
     *          format="float",
     *          example="12",
     *     ),
     *     @OA\Property(
     *          property="materials_used",
     *          description="Total amount of used materials for job",
     *          type="number",
     *          format="float",
     *          example="12",
     *     ),
     *     @OA\Property(
     *          property="po_and_other_used",
     *          description="Total amount of used po and others for job",
     *          type="number",
     *          format="float",
     *          example="12",
     *     ),
     *     @OA\Property(
     *          property="assessment_reports",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"date","report","total_amount","status"},
     *              @OA\Property(
     *                  property="id",
     *                  description="Assessment report identifier",
     *                  type="integer",
     *                  example=1,
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  description="Date",
     *                  type="string",
     *                  format="date",
     *                  example="2018-11-10"
     *              ),
     *              @OA\Property(
     *                  property="report",
     *                  description="AR name",
     *                  type="string",
     *                  example="Assessment report"
     *              ),
     *              @OA\Property(
     *                  property="total_amount",
     *                  description="Total amount of specific AR",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="AR current status",
     *                  type="string",
     *                  example="Draft"
     *              ),
     *          ),
     *     ),
     * ),
     *
     * @OA\Property(
     *     property="data",
     *     ref="#/components/schemas/CostingSummary",
     *     description="Costing summary for specific job",
     * ),
     */
    protected $data;
}
