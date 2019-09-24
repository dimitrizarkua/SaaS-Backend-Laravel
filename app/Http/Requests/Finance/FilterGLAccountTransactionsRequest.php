<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use App\Rules\UserHasAccessToGlAccount;
use OpenApi\Annotations as OA;

/**
 * Class FilterGLAccountTransactionsRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"gl_account_id"},
 *     @OA\Property(
 *         property="gl_account_id",
 *         description="GL account identifier.",
 *         type="int",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="date_from",
 *         description="Defines start date for filtering transactions by account.",
 *         type="string",
 *         format="date",
 *         example="2019-01-01"
 *     ),
 *     @OA\Property(
 *         property="date_to",
 *         description="Defines end date for filtering transactions by account.",
 *         type="string",
 *         format="date",
 *         example="2019-02-02"
 *     ),
 * )
 */
class FilterGLAccountTransactionsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'gl_account_id' => [
                'required',
                'integer',
                'exists:gl_accounts,id',
                new UserHasAccessToGlAccount($this->user()),
            ],
            'date_from'     => 'string|date_format:Y-m-d',
            'date_to'       => 'string|date_format:Y-m-d',
        ];
    }

    /**
     * Returns gl account identifier.
     *
     * @return int
     */
    public function getGlAccountId(): int
    {
        return $this->get('gl_account_id');
    }
}
