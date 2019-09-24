<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\ContactCategory;
use OpenApi\Annotations as OA;

/**
 * Class UpdateInsurerContractRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="contact_id",
 *         description="Identifier of contact",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="contract_number",
 *         description="Number of insurer contract",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Insurer contract description",
 *         type="string",
 *         example="Some text about contract.",
 *     ),
 *     @OA\Property(
 *          format="date",
 *          property="effect_date",
 *          type="string",
 *          description="",
 *          example="2018-11-10"
 *     ),
 *     @OA\Property(
 *          property="termination_date",
 *          description="",
 *          type="string",
 *          format="date",
 *          example="2018-11-10"
 *     ),
 * )
 */
class UpdateInsurerContractRequest extends ApiRequest
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
            'contract_number'  => 'string',
            'description'      => 'string',
            'effect_date'      => 'date',
            'termination_date' => 'date',
        ];
    }
}
