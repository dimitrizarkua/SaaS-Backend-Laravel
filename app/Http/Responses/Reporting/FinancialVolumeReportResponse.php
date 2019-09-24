<?php

namespace App\Http\Responses\Reporting;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FinancialVolumeReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class FinancialVolumeReportResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="FinancialVolumeReport",
     *     type="object",
     *     @OA\Property(
     *          property="total_revenue",
     *          description="Sum of total amounts paid on invoices for jobs",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="total_revenue_change",
     *          description="Percentage change of total revenue",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="from_jobs",
     *          description="Number of jobs for selected date period",
     *          type="integer",
     *          example="123",
     *     ),
     *     @OA\Property(
     *          property="from_jobs_change",
     *          description="Percentage change of jobs",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="invoices",
     *          description="Number of invoices for jobs",
     *          type="integer",
     *          example="123",
     *     ),
     *     @OA\Property(
     *          property="invoices_change",
     *          description="Percentage change of invoices",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="purchase_orders",
     *          description="Number of purchase orders for jobs",
     *          type="integer",
     *          example="123",
     *     ),
     *     @OA\Property(
     *          property="purchase_orders_change",
     *          description="Percentage change of purchase orders",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="credit_notes",
     *          description="Number of credit notes for jobs",
     *          type="integer",
     *          example="123",
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
     *          description="Total gross profit for jobs",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="total_gross_profit_change",
     *          description="Percentage change of total gross profit",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="accounts_receivable",
     *          description="Sum of the total invoices for jobs",
     *          type="number",
     *          format="float",
     *          example="1.32"
     *     ),
     *     @OA\Property(
     *          property="accounts_receivable_change",
     *          description="Percentage change of accounts receivable",
     *          type="number",
     *          format="float",
     *          example="1.32"
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
     *     ref="#/components/schemas/FinancialVolumeReport",
     *     description="Financial volume report",
     * ),
     */
    protected $data;
}
