<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class ScheduleTaskRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"starts_at","ends_at"},
 *     @OA\Property(
 *          property="starts_at",
 *          description="Starts at time",
 *          type="string",
 *          format="date-time",
 *          example="2018-11-10T09:10:11Z"
 *     ),
 *     @OA\Property(
 *          property="ends_at",
 *          description="Ends at time",
 *          type="string",
 *          format="date-time",
 *          example="2018-11-10T09:10:11Z"
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class ScheduleTaskRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'starts_at' => 'required|date_format:Y-m-d\TH:i:s\Z',
            'ends_at'   => 'required|date_format:Y-m-d\TH:i:s\Z|after_or_equal:starts_at',
        ];
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getStartsAt(): Carbon
    {
        return Carbon::make($this->get('starts_at'));
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function getEndsAt(): Carbon
    {
        return Carbon::make($this->get('ends_at'));
    }
}
