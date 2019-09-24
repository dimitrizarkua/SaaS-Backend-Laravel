<?php

namespace App\Http\Requests\Finance;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\ContactCategory;
use OpenApi\Annotations as OA;

/**
 * Class CreateAccountingOrganizationRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"contact_id", "lock_day_of_month","location_id"},
 *     @OA\Property(
 *         property="contact_id",
 *         description="Identifier of a contact which is considered as a primary contact in this accounting
 *         organization",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="lock_day_of_month",
 *         description="End-of-month financial date",
 *         type="integer",
 *         example="14"
 *     ),
 *     @OA\Property(
 *         property="cc_payments_api_key",
 *         description="Payments API key",
 *         type="string",
 *         example="API KEY"
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         description="Location id",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class CreateAccountingOrganizationRequest extends ApiRequest
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
            'contact_id'          => [
                'required',
                'integer',
                new ContactCategory(ContactCategoryTypes::COMPANY_LOCATION),
            ],
            'lock_day_of_month'   => 'required|integer|between:1,31',
            'location_id'         => 'required|integer|exists:locations,id',
            'cc_payments_api_key' => 'string',
        ];
    }
}
