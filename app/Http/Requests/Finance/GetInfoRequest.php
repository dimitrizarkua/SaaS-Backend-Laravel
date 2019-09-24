<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;

/**
 * Class GetInfoRequest
 *
 * @package App\Http\Requests\Finance
 */
class GetInfoRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'locations'   => 'array',
            'locations.*' => 'integer|exists:locations,id',
        ];
    }

    /**
     * @return array
     */
    public function getLocationIds(): array
    {
        $locationIds = $this->input('locations', []);

        if (empty($locationIds)) {
            $locationIds = auth()->user()
                ->locations
                ->pluck('id')
                ->toArray();
        }

        return $locationIds;
    }
}
