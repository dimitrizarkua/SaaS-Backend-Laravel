<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class StaffRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"date"},
 *     @OA\Property(
 *          property="date",
 *          description="Date on which the search is made",
 *          type="string",
 *          format="date",
 *          example="2018-01-01"
 *     ),
 * )
 *
 * @package App\Http\Requests\Operations
 */
class StaffRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
        ];
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return Carbon::make($this->get('date'));
    }
}
