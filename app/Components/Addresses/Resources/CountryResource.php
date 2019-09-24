<?php

namespace App\Components\Addresses\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CountryResource
 *
 * @package App\Components\Addresses\Resources
 * @mixin \App\Components\Addresses\Models\Country
 */
class CountryResource extends JsonResource
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
            'id'              => $this->id,
            'name'            => $this->name,
            'iso_alpha2_code' => $this->iso_alpha2_code,
            'iso_alpha3_code' => $this->iso_alpha3_code,
        ];
    }
}
