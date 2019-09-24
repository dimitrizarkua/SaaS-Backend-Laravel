<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Models\Filters\PurchaseOrderListingFilter;
use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class FilterPurchaseOrderListingsRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="locations",
 *          type="array",
 *          @OA\Items(
 *              type="integer",
 *              description="location id",
 *              example=1
 *          ),
 *     ),
 *     @OA\Property(
 *        property="recipient_contact_id",
 *        description="Identifier of recipient contact",
 *        type="integer",
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="job_id",
 *        description="Identifier of job",
 *        type="integer",
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="date_from",
 *        description="Date from",
 *        type="string",
 *        format="date",
 *        example="2018-11-10"
 *     ),
 *     @OA\Property(
 *        property="date_to",
 *        description="Date to",
 *        type="string",
 *        format="date",
 *        example="2018-11-30"
 *     ),
 * )
 */
class FilterPurchaseOrderListingsRequest extends ApiRequest
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
            'locations'            => 'array',
            'locations.*'          => 'integer|exists:locations,id',
            'recipient_contact_id' => 'integer|exists:contacts,id',
            'job_id'               => 'integer|exists:jobs,id',
            'date_from'            => 'string|date_format:Y-m-d',
            'date_to'              => 'string|date_format:Y-m-d',
        ];
    }

    /**
     * @return \App\Components\Finance\Models\Filters\PurchaseOrderListingFilter
     * @throws \JsonMapper_Exception
     */
    public function getPurchaseOrderListingFilter(): PurchaseOrderListingFilter
    {
        $filter          = new PurchaseOrderListingFilter($this->validated());
        $filter->user_id = $this->user()->id;

        return $filter;
    }
}
