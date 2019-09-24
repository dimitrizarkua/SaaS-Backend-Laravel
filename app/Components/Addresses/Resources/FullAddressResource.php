<?php

namespace App\Components\Addresses\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullAddressResource
 *
 * @package App\Components\Addresses\Resources
 * @mixin \App\Components\Addresses\Models\Address
 *
 * @OA\Schema(
 *     type="object",
 *     nullable=true,
 *     required={"id","address_line_1"},
 * )
 */
class FullAddressResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="Address Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="contact_name",
     *     description="Contact name",
     *     type="string",
     *     example="Daniel McKenzie",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="contact_phone",
     *     description="Contact phone number",
     *     type="string",
     *     example="0413456989",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="address_line_1",
     *     description="Address line 1",
     *     type="string",
     *     example="143 Mason St",
     * ),
     * @OA\Property(
     *     property="address_line_2",
     *     description="Address line 2",
     *     type="string",
     *     example="143 Mason St",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="full_address",
     *     description="Full address string representation",
     *     type="string",
     *     example="143 Mason St, Aarons Pass NSW 2850"
     * ),
     * @OA\Property(
     *      property="suburb",
     *      type="object",
     *      nullable=true,
     *      required={"id","name","postcode"},
     *      description="Suburb model",
     *      @OA\Property(
     *          property="id",
     *          description="Suburb Identifier",
     *          type="integer",
     *          example="1"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          description="Suburb name",
     *          type="string",
     *          example="Aarons Pass"
     *      ),
     *      @OA\Property(
     *          property="postcode",
     *          description="Suburb postcode",
     *          type="string",
     *          example="2850"
     *      ),
     *      @OA\Property(
     *          property="state",
     *          description="Related state model",
     *          type="object",
     *          @OA\Property(
     *              property="id",
     *              description="State Identifier",
     *              type="integer",
     *              example="1"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="State name",
     *              type="string",
     *              example="New South Wales"
     *          ),
     *          @OA\Property(
     *              property="code",
     *              description="State code",
     *              type="string",
     *              example="NSW"
     *          ),
     *          @OA\Property(
     *              property="country",
     *              description="Related country model",
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="CountryIdentifier",
     *                  type="integer",
     *                  example="1"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Country name",
     *                  type="string",
     *                  example="Australia"
     *              ),
     *              @OA\Property(
     *                  property="iso_alpha2_code",
     *                  description="Two-letter country code",
     *                  type="string",
     *                  example="AU"
     *              ),
     *              @OA\Property(
     *                  property="iso_alpha3_code",
     *                  description="Three-letter country code",
     *                  type="string",
     *                  example="AUS"
     *              ),
     *          ),
     *      ),
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();
        unset($result['suburb_id']);
        $result['suburb'] = SuburbResource::make($this->suburb);

        return $result;
    }
}
