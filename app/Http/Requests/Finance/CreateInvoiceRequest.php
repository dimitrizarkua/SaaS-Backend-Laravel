<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use OpenApi\Annotations as OA;

/**
 * Class CreateInvoiceRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "location_id",
 *          "accounting_organization_id",
 *          "recipient_contact_id",
 *          "recipient_address",
 *          "recipient_name",
 *          "payment_terms_days",
 *          "due_at",
 *     },
 *     @OA\Property(
 *        property="location_id",
 *        description="Location identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="recipient_contact_id",
 *        description="Identifier of contact which is recipeint of invoice",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="recipient_address",
 *        description="Recipient address",
 *        type="string",
 *        example="300 Collins Street, Brisbane QLD 8000",
 *     ),
 *     @OA\Property(
 *        property="recipient_name",
 *        description="Recipient name",
 *        type="string",
 *        example="Joshua Brown",
 *     ),
 *     @OA\Property(
 *        property="job_id",
 *        description="Job identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *         property="date",
 *         description="Invoice date",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 *     @OA\Property(
 *         property="due_at",
 *         description="Due at",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z"
 *     ),
 *     @OA\Property(
 *         property="reference",
 *         description="Reference",
 *         type="string",
 *         example="Some reference",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *        property="items",
 *        description="Invoice items that should be attached to the invoice",
 *        type="array",
 *        @OA\Items(ref="#/components/schemas/AddInvoiceItemRequest")
 *     ),
 * )
 */
class CreateInvoiceRequest extends ApiRequest
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
        $ruleSet = [
            'location_id'                => [
                'required',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'recipient_contact_id'       => 'required|integer|exists:contacts,id',
            'recipient_address'          => 'string',
            'recipient_name'             => 'string',
            'job_id'                     => 'nullable|integer|exists:jobs,id',
            'date'                       => 'required|date_format:Y-m-d',
            'due_at'                     => 'required|date_format:Y-m-d\TH:i:s\Z',
            'reference'                  => 'string',
        ];

        return array_merge(
            $ruleSet,
            arrayMapKeys($this->invoiceItemRules(), function ($key) {
                return 'items.*.' . $key;
            })
        );
    }

    /**
     * @return array
     */
    private function invoiceItemRules(): array
    {
        $invoiceItemValidator = new AddInvoiceItemRequest();

        return $invoiceItemValidator->rules();
    }
}
