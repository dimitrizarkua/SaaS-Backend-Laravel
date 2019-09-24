<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullTransactionResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\Transaction
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Transaction")},
 * )
 */
class FullTransactionResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="records",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullTransactionRecordResource")
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
        $result            = $this->resource->toArray();
        $result['records'] = [];

        foreach ($this->records as $record) {
            $result['records'][] = FullTransactionRecordResource::make($record);
        }

        return $result;
    }
}
