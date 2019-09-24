<?php

namespace App\Http\Responses\Reporting;

use OpenApi\Annotations as OA;
use App\Http\Responses\ApiOKResponse;

/**
 * Class GSTReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class GSTReportResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="object",
     *     @OA\Property(
     *         type="object",
     *         @OA\Property(
     *            property="income",
     *            type="object",
     *            @OA\Property(
     *              property="data",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      type="string",
     *                      property="name",
     *                      description="Tax name",
     *                      example="GST on Income"
     *                  ),
     *                  @OA\Property(
     *                      property="total_amount",
     *                      type="number",
     *                      format="float",
     *                      description="Total amount (include taxes)",
     *                      example=5576.10
     *                  ),
     *                  @OA\Property(
     *                      property="taxes",
     *                      type="number",
     *                      format="float",
     *                      description="Taxes",
     *                      example=0
     *                  ),
     *                  @OA\Property(
     *                      property="taxe_rate",
     *                      type="integer",
     *                      description="Tax rate in percentages",
     *                      example=0
     *                  ),
     *              )
     *            ),
     *            @OA\Property(
     *              property="total",
     *              type="number",
     *              format="float",
     *              description="Total amount for the group",
     *              example=5576.10
     *            ),
     *         ),
     *         @OA\Property(
     *            property="expenses",
     *            type="object",
     *            @OA\Property(
     *              property="data",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      type="string",
     *                      property="name",
     *                      description="Tax name",
     *                      example="GST on Expenses"
     *                  ),
     *                  @OA\Property(
     *                      property="total_amount",
     *                      type="number",
     *                      format="float",
     *                      description="Total amount (include taxes)",
     *                      example=5576.10
     *                  ),
     *                  @OA\Property(
     *                      property="taxes",
     *                      type="number",
     *                      format="float",
     *                      description="Taxes",
     *                      example=0
     *                  ),
     *                  @OA\Property(
     *                      property="taxe_rate",
     *                      type="integer",
     *                      description="Tax rate in percentages",
     *                      example=0
     *                  ),
     *              )
     *            ),
     *            @OA\Property(
     *              property="total",
     *              type="number",
     *              format="float",
     *              description="Total amount for the group",
     *              example=5576.10
     *            ),
     *         ),
     *     )
     *  )
     */
    protected $data;
}
