<?php

namespace App\Components\Contacts\Resources;

use App\Components\Contacts\Models\Enums\ContactTypes;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class BaseContactResource
 *
 * @OA\Schema(
 *     oneOf={
 *         @OA\Schema(ref="#/components/schemas/ContactPersonProfile"),
 *         @OA\Schema(ref="#/components/schemas/ContactCompanyProfile"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class BaseContactResource extends JsonResource
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
        $result = $this->resource->toArray();

        if (ContactTypes::PERSON === $this->contact_type && $this->person) {
            $result += $this->person->toArray();
        }

        if (ContactTypes::COMPANY === $this->contact_type && $this->company) {
            $result += $this->company->toArray();
        }

        unset($result['person'], $result['company']);

        return $result;
    }
}
