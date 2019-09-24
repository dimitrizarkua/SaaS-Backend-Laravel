<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class TransactionRecordResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\TransactionRecord
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/TransactionRecord")},
 * )
 */
class TransactionRecordResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="balance",
     *     type="number",
     *     example="23.33"
     * ),
     * @OA\Property(
     *     property="transaction",
     *     type="object",
     *     allOf={@OA\Schema(ref="#/components/schemas/Transaction")},
     * ),
     */
}
