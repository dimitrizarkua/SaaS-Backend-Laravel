<?php

namespace App\Http\Requests\Meetings;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateMeetingRequest
 *
 * @package App\Http\Requests\CreateMeetingRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"title", "scheduled_at"},
 *     @OA\Property(
 *         property="title",
 *         description="Meeting title",
 *         type="string",
 *         example="Weekly meeting"
 *     ),
 *     @OA\Property(
 *         property="scheduled_at",
 *         description="Meeting date",
 *         type="date",
 *         example="2018-11-10T09:10:11Z"
 *     )
 * )
 */
class CreateMeetingRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'        => 'required|string',
            'scheduled_at' => 'required|date_format:Y-m-d\TH:i:s\Z',
        ];
    }
}
