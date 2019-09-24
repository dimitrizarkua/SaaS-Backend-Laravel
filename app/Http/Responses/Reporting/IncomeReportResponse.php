<?php

namespace App\Http\Responses\Reporting;

use App\Http\Responses\ApiOKResponse;

/**
 * Class IncomeReportResponse
 * Response for Finance: Report - Income by Account Summary.
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class IncomeReportResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="object",
     *     @OA\Property(
     *         type="object",
     *         required={"account_types", "total_amount", "total_forwarded_amount"},
     *         @OA\Property(
     *            property="total_amount",
     *            type="number",
     *            format="float",
     *            example="1.11",
     *            description="Total amount for GL accounts. Total amount = subtotal by account_types +
     *            total_forwarded_amount.",
     *         ),
     *         @OA\Property(
     *            property="total_forwarded_amount",
     *            type="number",
     *            format="float",
     *            example="1.11",
     *            description="Total forwarded amount.",
     *         ),
     *         @OA\Property(
     *            property="account_types",
     *            type="array",
     *            description="GL Account types",
     *            @OA\Items(
     *              type="object",
     *              description="GL accounts grouped by type.",
     *              required={"name", "accounts"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="GL Account name",
     *                  example="BEN Royalties: 104556-01"
     *              ),
     *              @OA\Property(
     *                  property="accounts",
     *                  type="object",
     *                  description="GL Accounts data",
     *                  required={"subtotal_amount", "items"},
     *                  @OA\Property(
     *                      property="subtotal_amount",
     *                      type="number",
     *                      format="float",
     *                      description="Sub total amount by GL accounts grouped by type.",
     *                      example="1.11"
     *                  ),
     *                  @OA\Property(
     *                      property="items",
     *                      type="array",
     *                      description="GL Accounts data.",
     *                      @OA\Items(
     *                          type="object",
     *                          required={"name", "amount_ex_tax"},
     *                          @OA\Property(
     *                              property="name",
     *                              type="string",
     *                              description="GL Account name",
     *                              example="BEN Royalties: 104556-01"
     *                          ),
     *                          @OA\Property(
     *                              property="amount_ex_tax",
     *                              type="number",
     *                              format="float",
     *                              description="Income by account exclude tax.",
     *                              example="1.11"
     *                          )
     *                      )
     *                   )
     *              )
     *            )
     *         )
     *     )
     *  )
     */
    protected $data;
}
