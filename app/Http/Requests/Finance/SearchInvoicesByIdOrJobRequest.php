<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;

/**
 * Class SearchInvoicesByIdOrJobRequest
 *
 * @package App\Http\Requests\Finance
 */
class SearchInvoicesByIdOrJobRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'numbers'   => 'required|array',
            'numbers.*' => 'integer',
        ];
    }

    /**
     * @return array
     */
    public function getNumbers(): array
    {
        return $this->input('numbers');
    }
}
