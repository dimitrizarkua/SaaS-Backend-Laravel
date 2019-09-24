<?php

namespace App\Http\Requests\Jobs;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\ContactCategory;
use App\Rules\RecurrenceRule;
use OpenApi\Annotations as OA;

/**
 * Class CreateRecurringJobRequest
 *
 * @package App\Http\Requests\Jobs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"recurrence_rule", "job_service_id", "insurer_id", "site_address_id",
 *     "owner_location_id","description" },
 *     @OA\Property(
 *         property="recurrence_rule",
 *         description="Recurrence rules according to https://tools.ietf.org/html/rfc5545.",
 *         type="string",
 *         example="FREQ=YEARLY;INTERVAL=2;COUNT=3"
 *      ),
 *      @OA\Property(
 *         property="job_service_id",
 *         description="Identifier of related service",
 *         type="integer",
 *         example="1"
 *      ),
 *      @OA\Property(
 *         property="insurer_id",
 *         description="Insurer contact identifier",
 *         type="integer",
 *         example="1"
 *      ),
 *      @OA\Property(
 *         property="site_address_id",
 *         description="Site address identifier",
 *         type="integer",
 *         example="1"
 *      ),
 *      @OA\Property(
 *         property="owner_location_id",
 *         description="Owner location identifier",
 *         type="integer",
 *         example="1"
 *      ),
 *      @OA\Property(
 *         property="description",
 *         description="Recurring job description",
 *         type="string",
 *         example="Recurring job description"
 *      )
 * )
 */
class CreateRecurringJobRequest extends ApiRequest
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
            'recurrence_rule'   => ['required', 'string', new RecurrenceRule()],
            'job_service_id'    => 'required|integer|exists:job_services,id',
            'insurer_id'        => [
                'integer',
                new ContactCategory(ContactCategoryTypes::INSURER),
            ],
            'site_address_id'   => 'required|integer|exists:addresses,id',
            'owner_location_id' => 'required|integer|exists:locations,id',
            'description'       => 'required|string',
        ];
    }
}
