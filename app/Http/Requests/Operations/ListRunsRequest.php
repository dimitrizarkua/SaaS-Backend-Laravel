<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class ListRunsRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id","date"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="date",
 *          type="string",
 *          format="date",
 *          description="Requested date",
 *          example="2018-11-10"
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class ListRunsRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|integer',
            'date'        => 'required|date',
        ];
    }

    /**
     * @return integer
     */
    public function getLocationId(): int
    {
        return $this->get('location_id');
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return Carbon::make($this->get('date'));
    }
}
