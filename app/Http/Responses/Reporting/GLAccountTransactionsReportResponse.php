<?php

namespace App\Http\Responses\Reporting;

use App\Components\Finance\Resources\TransactionRecordResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class GLAccountTransactionsReportResponse
 *
 * @package App\Http\Responses\Reporting
 * @OA\Schema(required={"data"})
 */
class GLAccountTransactionsReportResponse extends ApiOKResponse
{
    protected $resource = TransactionRecordResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TransactionRecordResource")
     * ),
     * @var \App\Components\Finance\Resources\TransactionRecordResource[]
     */
    protected $data;

    /**
     * @OA\Property(
     *     property="additional",
     *     type="object",
     *     nullable=true,
     *     @OA\Property(
     *         property="gl_account",
     *         ref="#/components/schemas/GLAccount"
     *     ),
     *     @OA\Property(
     *         property="total_transactions",
     *         description="Count of transactions.",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="total_balance",
     *         description="Total balance on the end of the period.",
     *         type="number"
     *     )
     * )
     */
    protected $additional;
}
