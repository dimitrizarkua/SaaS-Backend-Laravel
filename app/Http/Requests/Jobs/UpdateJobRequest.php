<?php

namespace App\Http\Requests\Jobs;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Jobs\Enums\ClaimTypes;
use App\Components\Jobs\Enums\JobCriticalityTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\ContactCategory;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="claim_number",
 *         description="Claim number",
 *         type="string",
 *         nullable=true,
 *         example="#10198747-MEL"
 *     ),
 *     @OA\Property(
 *         property="job_service_id",
 *         description="Identifier of related service",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="insurer_id",
 *         description="Identifier of issuer",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="site_address_id",
 *         description="Identifier of site address",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="site_address_lat",
 *         description="Latitude of site address",
 *         type="number",
 *         example="-37.815018"
 *     ),
 *     @OA\Property(
 *         property="site_address_lng",
 *         description="Longitude of site address",
 *         type="number",
 *         example="144.946014"
 *     ),
 *     @OA\Property(
 *         property="assigned_location_id",
 *         description="Identifier of assigned location",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="owner_location_id",
 *         description="Identifier of owner location",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="reference_number",
 *         description="Reference number",
 *         type="string",
 *         example="#reference_number"
 *     ),
 *     @OA\Property(
 *         property="claim_type",
 *         ref="#/components/schemas/ClaimTypes"
 *     ),
 *     @OA\Property(
 *         property="criticality",
 *         ref="#/components/schemas/JobCriticalityTypes"
 *     ),
 *     @OA\Property(
 *         property="date_of_loss",
 *         description="Date of loss",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="initial_contact_at",
 *         description="Initial contact at",
 *         type="string",
 *         format="date",
 *         example="2018-11-10T09:10:11Z"
 *     ),
 *     @OA\Property(
 *         property="cause_of_loss",
 *         description="Cause of loss",
 *         type="string",
 *         example="Some cause"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Description",
 *         type="string",
 *         example="Clean up water, dry out kitchen cabinetry and timber flooring"
 *     ),
 *     @OA\Property(
 *         property="anticipated_revenue",
 *         description="Anticipated revenue",
 *         type="number",
 *         example="5000"
 *     ),
 *     @OA\Property(
 *         property="anticipated_invoice_date",
 *         description="Anticipated invoice date",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="expected_excess_payment",
 *         description="Expected excess payment",
 *         type="number",
 *         example="1000"
 *     ),
 *     @OA\Property(
 *         property="authority_received_at",
 *         description="Authority received at",
 *         type="string",
 *         format="date",
 *         example="2018-11-10T09:10:11Z"
 *     ),
 * )
 */
class UpdateJobRequest extends ApiRequest
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
        $job = $this->route('job');

        return [
            'claim_number'             => [
                'nullable',
                'string',
                'unique:jobs,claim_number,' . $job->claim_number . ',claim_number',
            ],
            'job_service_id'           => 'nullable|integer|exists:job_services,id',
            'insurer_id'               => [
                'nullable',
                'integer',
                new ContactCategory(ContactCategoryTypes::INSURER),
            ],
            'site_address_id'          => 'nullable|integer|exists:addresses,id',
            'site_address_lat'         => 'nullable|numeric|min:-90|max:90',
            'site_address_lng'         => 'nullable|numeric|min:-180|max:180',
            'assigned_location_id'     => 'nullable|integer|exists:locations,id',
            'owner_location_id'        => 'nullable|integer|exists:locations,id',
            'reference_number'         => 'nullable|string',
            'claim_type'               => ['nullable', 'string', Rule::in(ClaimTypes::values())],
            'criticality'              => ['nullable', 'string', Rule::in(JobCriticalityTypes::values())],
            'date_of_loss'             => 'nullable|date',
            'initial_contact_at'       => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'cause_of_loss'            => 'nullable|string',
            'description'              => 'nullable|string',
            'anticipated_revenue'      => 'nullable|numeric',
            'anticipated_invoice_date' => 'nullable|date',
            'authority_received_at'    => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'expected_excess_payment'  => 'nullable|numeric',
        ];
    }
}
