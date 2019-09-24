<?php

namespace App\Components\Addresses\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class StateResource
 *
 * @package App\Components\Addresses\Resources
 * @mixin \App\Components\Addresses\Models\State
 */
class StateResource extends JsonResource
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
            'id'      => $this->id,
            'name'    => $this->name,
            'code'    => $this->code,
            'country' => CountryResource::make($this->country),
        ];
    }
}
