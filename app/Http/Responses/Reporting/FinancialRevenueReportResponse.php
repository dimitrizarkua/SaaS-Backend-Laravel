<?php

namespace App\Http\Responses\Reporting;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FinancialRevenueReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class FinancialRevenueReportResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="FinancialRevenueReport",
     *     type="object",
     *     @OA\Property(
     *          property="invoices_paid",
     *          description="Total sum of all payments made on invoices with the period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="invoices_paid_change",
     *          description="Percentage change of invoices paid",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="invoices_written",
     *          description="Total sum of all approved invoices",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="invoices_written_change",
     *          description="Percentage change of invoices written",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="avg_job_cost",
     *          description="Average cost of all jobs within the period",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="avg_job_cost_change",
     *          description="Percentage change of avg. job cost",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="avg_over_job_cost",
     *          description="Average amount that jobs have gone over budget",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="avg_over_job_cost_change",
     *          description="Percentage change of avg. over job cost",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="credit_notes",
     *          description="Total sum of all approved credit notes",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="credit_notes_change",
     *          description="Percentage change of credit notes",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="total_gross_profit",
     *          description="Total gross profit for period",
     *          type="number",
     *          format="float",
     *          example="123",
     *     ),
     *     @OA\Property(
     *          property="total_gross_profit_change",
     *          description="Percentage change of total gross profit",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="tagged_invoices",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"name","count","percent","change"},
     *              @OA\Property(
     *                  property="name",
     *                  description="Tag name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="count",
     *                  description="Number of uses per period",
     *                  type="integer",
     *                  example="23",
     *              ),
     *              @OA\Property(
     *                  property="percent",
     *                  description="Percentage used compared to all tags",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
     *              ),
     *              @OA\Property(
     *                  property="change",
     *                  description="Difference between percentage used in percent to previous period",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
     *              ),
     *          ),
     *     ),
     *     @OA\Property(
     *          property="revenue_accounts",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              required={"name","code","amount"},
     *              @OA\Property(
     *                  property="name",
     *                  description="Account name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="code",
     *                  description="Account code",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="amount",
     *                  description="Account balance within the current period.",
     *                  type="number",
     *                  format="float",
     *                  example="10.44",
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
     *                  description="Total revenue for a specific day (ordinate)",
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
     *     ref="#/components/schemas/FinancialRevenueReport",
     *     description="Financial revenue report",
     * ),
     */
    protected $data;
}
