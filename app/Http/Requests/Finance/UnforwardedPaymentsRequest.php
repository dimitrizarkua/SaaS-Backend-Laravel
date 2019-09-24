<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;

/**
 * Class UnforwardedPaymentsRequest
 *
 * @package App\Http\Requests\Finance
 */
class UnforwardedPaymentsRequest extends ApiRequest
{
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
        ];
    }

    /**
     * Returns location identifier.
     *
     * @return integer
     */
    public function getLocationId(): int
    {
        return $this->get('location_id');
    }
}
