<?php

namespace App\Components\Finance\Resources;

use App\Components\Finance\Models\FinancialEntity;
use App\Components\Jobs\Resources\FullJobResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FinanceEntityListResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","location_id","recipient_name","total_amount","virtual_status","latest_status","date"},
 * )
 *
 * @property FinancialEntity $resource
 */
class FinanceEntityListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="Entity identifier",
     *     example="1"
     * ),
     * @OA\Property(
     *    property="location_id",
     *    description="Location identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="recipient_name",
     *    description="Recipient name",
     *    type="string",
     *    example="Joshua Brown",
     * ),
     * @OA\Property(
     *    property="total_amount",
     *    description="Sum of amount of all items with taxes",
     *    type="number",
     *    example=1456.00,
     * ),
     * @OA\Property(
     *    property="date",
     *    description="Date",
     *    type="string",
     *    format="date",
     *    example="2018-11-10"
     * ),
     * @OA\Property(
     *     property="job",
     *     type="object",
     *     nullable=true,
     *     @OA\Schema(
     *         ref="#/components/schemas/FullJobResource"
     *     )
     * ),
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/FinancialEntityStatusResource",
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
        $data = [
            'id'             => $this->resource->id,
            'location_id'    => $this->resource->location_id,
            'recipient_name' => $this->resource->recipient_name,
            'total_amount'   => $this->resource->getTotalAmount(),
            'job'            => FullJobResource::make($this->resource->job),
            'virtual_status' => $this->resource->getVirtualStatus(),
            'latest_status'  => FinancialEntityStatusResource::make($this->resource->getLatestStatus()),
            'date'           => $this->resource->date->format('Y-m-d'),
        ];

        return $data;
    }
}
