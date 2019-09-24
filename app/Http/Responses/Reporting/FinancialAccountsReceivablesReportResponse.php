<?php

namespace App\Http\Responses\Reporting;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FinancialAccountsReceivablesReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class FinancialAccountsReceivablesReportResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="FinancialAccountsReceivablesReport",
     *     type="object",
     *     @OA\Property(
     *          property="current",
     *          description="Amount of receivables for period from current to 29 days ago",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="current_change",
     *          description="Percentage change for current period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_30_days",
     *          description="Amount of receivables for period from 30 days ago to 59 days ago",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_30_days_change",
     *          description="Percentage change for more_30_days period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_60_days",
     *          description="Amount of receivables for period from 60 days ago to 89 days ago",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_60_days_change",
     *          description="Percentage change for more_60_days period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_90_days",
     *          description="Amount of receivables more than 90 days ago",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="more_90_days_change",
     *          description="Percentage change for more_90_days period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="total",
     *          description="Total amount of receivables",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="total_change",
     *          description="Percentage change of total amount",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="contacts",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"name","current","more_30_days","more_60_days","more_90_days","total"},
     *              @OA\Property(
     *                   property="name",
     *                   description="Name of contact",
     *                   type="string",
     *              ),
     *              @OA\Property(
     *                   property="current",
     *                   description="Amount of receivables for period from current to 29 days ago
     * for specific contact",
     *                   type="number",
     *                   format="float",
     *                   example="1.32"
     *              ),
     *              @OA\Property(
     *                   property="more_30_days",
     *                   description="Amount of receivables for period from 30 days ago to 59 days ago
     * for specific contact",
     *                   type="number",
     *                   format="float",
     *                   example="1.32"
     *              ),
     *              @OA\Property(
     *                   property="more_60_days",
     *                   description="Amount of receivables for period from 60 days ago to 89 days ago
     * for specific contact",
     *                   type="number",
     *                   format="float",
     *                   example="1.32"
     *              ),
     *              @OA\Property(
     *                   property="more_90_days",
     *                   description="Amount of receivables more than 90 days ago for specific contact",
     *                   type="number",
     *                   format="float",
     *                   example="1.32"
     *              ),
     *              @OA\Property(
     *                   property="total",
     *                   description="Total amount of receivables for specific contact",
     *                   type="number",
     *                   format="float",
     *                   example="1.32"
     *              ),
     *          ),
     *     ),
     *     @OA\Property(
     *          property="chart",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"date","value"},
     *              @OA\Property(
     *                  property="date",
     *                  description="Date",
     *                  type="string",
     *                  format="date",
     *                  example="2018-11-10"
     *              ),
     *              @OA\Property(
     *                  property="value",
     *                  description="Total receivables for a specific day (ordinate)",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
     *              ),
     *          ),
     *     ),
     *     @OA\Property(
     *          property="previous_interval_chart",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"date","value"},
     *              @OA\Property(
     *                  property="date",
     *                  description="Date",
     *                  type="string",
     *                  format="date",
     *                  example="2018-11-10"
     *              ),
     *              @OA\Property(
     *                  property="value",
     *                  description="Total receivables for a specific day (ordinate)",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
     *              ),
     *          ),
     *     ),
     * ),
     *
     * @OA\Property(
     *     property="data",
     *     ref="#/components/schemas/FinancialAccountsReceivablesReport",
     *     description="Financial accounts receivables report",
     * ),
     */
    protected $data;
}
