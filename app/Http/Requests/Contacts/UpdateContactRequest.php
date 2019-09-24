<?php

namespace App\Http\Requests\Contacts;

use App\Http\Requests\ApiRequest;
use App\Rules\FirstName;
use App\Rules\LastName;
use OpenApi\Annotations as OA;

/**
 * Class UpdateContactRequest
 *
 * @OA\Schema(type="object",)
 *
 * @package App\Http\Requests\Contacts
 */
class UpdateContactRequest extends ApiRequest
{
    /**
     * @OA\Property(
     *     property="email",
     *     description="Email",
     *     type="string",
     *     example="john.smith@gmail.com",
     * ),
     * @OA\Property(
     *     property="business_phone",
     *     description="Business phone",
     *     type="string",
     *     example="0398776000",
     * ),
     * @OA\Property(
     *     property="first_name",
     *     description="First name",
     *     type="string",
     *     example="John",
     * ),
     * @OA\Property(
     *     property="last_name",
     *     description="Last name",
     *     type="string",
     *     example="Smith",
     * ),
     * @OA\Property(
     *     property="job_title",
     *     description="Job title",
     *     type="string",
     *     example="Technician",
     * ),
     * @OA\Property(
     *     property="direct_phone",
     *     description="Direct phone",
     *     type="string",
     *     example="0398776000",
     * ),
     * @OA\Property(
     *     property="mobile_phone",
     *     description="Mobile phone",
     *     type="string",
     *     example="0398776000",
     * ),
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
        return [
            'email'                      => 'nullable|email',
            'business_phone'             => 'nullable|string',
            'first_name'                 => [new FirstName()],
            'last_name'                  => [new LastName()],
            'job_title'                  => 'nullable|string',
            'direct_phone'               => 'nullable|string',
            'mobile_phone'               => 'nullable|string',
            'legal_name'                 => 'string',
            'trading_name'               => 'nullable|string',
            'abn'                        => 'string',
            'website'                    => 'nullable|string',
            'default_payment_terms_days' => 'integer',
        ];
    }
}
