<?php

namespace App\Components\Reporting\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountTrialReportResourceItem
 *
 * @OA\Schema(
 *    type="object"
 * )
 *
 * @package App\Components\Reporting\Resources
 */
class GLAccountTrialReportResourceItem extends JsonResource
{
    /**
     * @OA\Property(
     *     property="name",
     *     description="GL Account name",
     *     type="string",
     *     example="NAB Business Cheque Account",
     * ),
     * @OA\Property(
     *     property="debit_amount",
     *     description="Amount of all debit transactions",
     *     type="number",
     *     format="float",
     *     example=2500.20,
     * ),
     * @OA\Property(
     *     property="credit_amount",
     *     description="Amount of all credit transactions",
     *     type="number",
     *     format="float",
     *     example=2500.20,
     * ),
     * @OA\Property(
     *     property="debit_amount_ytd",
     *     description="Amount of all debit transactions year to date",
     *     type="number",
     *     format="float",
     *     example=2500.20,
     * ),
     * @OA\Property(
     *     property="credit_amount_ytd",
     *     description="Amount of all credit transactions year to date",
     *     type="number",
     *     format="float",
     *     example=2500.20,
     * )
     */
}
