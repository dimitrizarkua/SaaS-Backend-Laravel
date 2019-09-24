<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class SearchStaffRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id","date","name"},
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="date",
 *          description="Date on which the search is made",
 *          type="string",
 *          format="date",
 *          example="2018-01-01"
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Name to search",
 *          example="John Smith"
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class SearchStaffRequest extends ApiRequest
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
            'date'        => 'required|date_format:Y-m-d',
            'name'        => 'required|string',
        ];
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->get('location_id');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return Carbon::make($this->get('date'));
    }
}
