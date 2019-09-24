<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullTransactionRecordResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\TransactionRecord
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/TransactionRecord")},
 * )
 */
class FullTransactionRecordResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="gl_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['gl_account'] = GLAccountResource::make($this->glAccount);

        return $result;
    }
}
