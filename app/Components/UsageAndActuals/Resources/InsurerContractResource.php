<?php

namespace App\Components\UsageAndActuals\Resources;

use App\Components\Contacts\Resources\ContactResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class InsurerContractResource
 *
 * @package App\Components\UsageAndActuals\Resources
 * @mixin \App\Components\UsageAndActuals\Models\InsurerContract
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/InsurerContract")},
 * )
 */
class InsurerContractResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="contact",
     *     ref="#/components/schemas/ContactResource"
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
        $result['contact'] = ContactResource::make($this->contact);

        return $result;
    }
}
