<?php

namespace App\Components\Addresses\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SuburbResource
 *
 * @package App\Components\Addresses\Resources
 * @mixin \App\Components\Addresses\Models\Suburb
 */
class SuburbResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'postcode' => $this->postcode,
            'state'    => StateResource::make($this->state),
        ];
    }
}
