<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;

/**
 * Class GLAccountsIndexRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @property null|bool $is_debit
 */
class GLAccountsIndexRequest extends ApiRequest
{
    protected $booleanFields = [
        'is_debit',
    ];

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'is_debit' => 'nullable|boolean',
        ];
    }
}
