<?php

namespace App\Components\Finance\Resources;

use App\Components\Contacts\Resources\ContactWithAddressResource;
use App\Components\Documents\Resources\DocumentResource;
use App\Components\Jobs\Resources\FullJobResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FinancialEntityResource
 *
 * @package App\Components\Finance\Resources
 *
 * @property  \App\Components\Finance\Models\FinancialEntity $resource
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "location",
 *          "accounting_organization",
 *          "recipient_contact",
 *          "latest_status",
 *          "sub_total",
 *          "taxes",
 *          "total_amount",
 *          "virtual_status"
 *     }
 * )
 */
class FinancialEntityResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="location",
     *     ref="#/components/schemas/Location",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="accounting_organization",
     *     ref="#/components/schemas/AccountingOrganizationResource",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="recipient_contact",
     *     ref="#/components/schemas/ContactWithAddressResource",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/FinancialEntityStatusResource",
     * ),
     * @OA\Property(
     *     property="statuses",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FinancialEntityStatusResource")
     * ),
     * @OA\Property(
     *     property="document",
     *     type="object",
     *     nullable=true,
     *     @OA\Schema(
     *         ref="#/components/schemas/DocumentResource",
     *     )
     * ),
     * @OA\Property(
     *     property="job",
     *     type="object",
     *     @OA\Schema(
     *         ref="#/components/schemas/FullJobResource",
     *     ),
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="virtual_status",
     *     ref="#/components/schemas/PurchaseOrderVirtualStatuses"
     * ),
     * @OA\Property(
     *     property="sub_total",
     *     type="number",
     *     description="Sub total (total amount of all items without taxes)",
     *     example=4300.15,
     * ),
     * @OA\Property(
     *     property="taxes",
     *     type="number",
     *     description="Sum of taxes amount of all items. Only GST on income tax calculated here.",
     *     example=430.02,
     * ),
     * @OA\Property(
     *     property="total_amount",
     *     type="number",
     *     description="Total amount (with taxes)",
     *     example=100.00,
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['location']                = $this->resource->location->toArray();
        $result['accounting_organization'] = AccountingOrganizationResource::make($this->accountingOrganization);
        $result['recipient_contact']       = ContactWithAddressResource::make($this->recipientContact);
        $result['latest_status']           = FinancialEntityStatusResource::make($this->latestStatus);
        $result['statuses']                = FinancialEntityStatusResource::collection($this->statuses);
        $result['job']                     = FullJobResource::make($this->resource->job);
        $result['document']                = DocumentResource::make($this->document);
        $result['virtual_status']          = $this->resource->getVirtualStatus();
        $result['sub_total']               = $this->resource->getSubTotalAmount();
        $result['taxes']                   = $this->resource->getTaxesAmount();
        $result['total_amount']            = $this->resource->getTotalAmount();

        return $result;
    }
}
