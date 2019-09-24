<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class SearchFinancialEntityRequest
 *
 * @package App\Http\Requests\Finance
 */
abstract class SearchFinancialEntityRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id'             => 'required|string',
            'virtual_status' => ['string', $this->getVirtualStatuses()],
            'status'         => ['string', Rule::in(FinancialEntityStatuses::values())],
            'locations'      => 'array',
            'locations.*'    => 'integer|exists:locations,id',
        ];
    }

    /**
     * Returns list of virtual statuses
     * @return array
     */
    abstract protected function getVirtualStatuses(): array;

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'id' => $this->input('id'),
        ];
    }

    /**
     * @return string|null
     */
    public function getVirtualStatus(): ?string
    {
        return $this->input('virtual_status');
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->input('status');
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
