<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use OpenApi\Annotations as OA;

/**
 * Class CreateEquipmentRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "barcode",
 *         "equipment_category_id",
 *         "make",
 *         "model",
 *         "serial_number",
 *     },
 *     @OA\Property(
 *         property="barcode",
 *         description="Barcode of equipment",
 *         type="string",
 *         example="978-0-9542246",
 *     ),
 *     @OA\Property(
 *         property="equipment_category_id",
 *         description="Equipment category identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         description="Location identifier",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="make",
 *         description="Manufacturer",
 *         type="string",
 *         example="DampRid",
 *     ),
 *     @OA\Property(
 *         property="model",
 *         description="Specific model",
 *         type="string",
 *         example="FG90 Moisture Absorber Easy-Fill",
 *     ),
 *     @OA\Property(
 *         property="serial_number",
 *         description="Serial number",
 *         type="string",
 *         example="4CE0460D0G",
 *     ),
 *     @OA\Property(
 *         property="last_test_tag_at",
 *         description="Last test date",
 *         type="string",
 *         format="date",
 *         example="2018-11-10T09:10:11Z",
 *         nullable=true,
 *     ),
 * )
 */
class CreateEquipmentRequest extends ApiRequest
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
            'barcode'               => 'required|string',
            'equipment_category_id' => 'required|integer|exists:equipment_categories,id',
            'location_id'           => [
                'nullable',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'make'                  => 'required|string',
            'model'                 => 'required|string',
            'serial_number'         => 'required|string',
            'last_test_tag_at'      => 'nullable|date_format:Y-m-d\TH:i:s\Z',
        ];
    }
}
