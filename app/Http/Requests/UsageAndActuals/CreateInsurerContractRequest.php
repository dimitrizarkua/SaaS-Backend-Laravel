<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\ContactCategory;
use OpenApi\Annotations as OA;

/**
 * Class CreateInsurerContractRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "contact_id",
 *          "contract_number",
 *          "effect_date"
 *     },
 *     @OA\Property(
 *        property="contact_id",
 *        description="Identifier of contact",
 *        type="integer",
 *        example=1
 *     ),
 *     @OA\Property(
 *        property="contract_number",
 *        description="Number of insurer contract",
 *        type="string",
 *     ),
 *     @OA\Property(
 *        property="description",
 *        description="Insurer contract description",
 *        type="string",
 *        example="Some text about contract.",
 *     ),
 *     @OA\Property(
 *         property="effect_date",
 *         description="Date when contract activated",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="termination_date",
 *         description="Date when contract terminated",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 * )
 */
class CreateInsurerContractRequest extends ApiRequest
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
            'contact_id'       => [
                'required',
                'integer',
                new ContactCategory(ContactCategoryTypes::INSURER),

            ],
            'contract_number'  => 'required|string',
            'description'      => 'string',
            'effect_date'      => 'required|date',
            'termination_date' => 'date',
        ];
    }
}
