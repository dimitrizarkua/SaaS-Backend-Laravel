<?php

namespace App\Http\Requests\Contacts;

use OpenApi\Annotations as OA;

/**
 * Class CreateCompanyRequest
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/CreateContactRequest")
 *     },
 *     required={"legal_name","abn","default_payment_terms_days"},
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreateCompanyRequest extends CreateContactRequest
{
    /**
     * @OA\Property(
     *     property="legal_name",
     *     description="Legal name",
     *     type="string",
     *     example="Yarra Valley Water",
     * ),
     * @OA\Property(
     *     property="trading_name",
     *     description="Trading name",
     *     type="string",
     *     example="Yarra Valley Water",
     * ),
     * @OA\Property(
     *     property="abn",
     *     description="Australian Business Number",
     *     type="string",
     *     example="89 897 456 578",
     * ),
     * @OA\Property(
     *     property="website",
     *     description="Website",
     *     type="string",
     *     example="yarra-valley-water.com.au",
     * ),
     * @OA\Property(
     *     property="default_payment_terms_days",
     *     description="Default payment terms",
     *     type="integer",
     *     example=30,
     * ),
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'legal_name'                 => 'required|string',
                'trading_name'               => 'nullable|string',
                'abn'                        => 'required|string',
                'website'                    => 'nullable|string',
                'default_payment_terms_days' => 'required|integer',
            ]
        );
    }
}
