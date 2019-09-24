<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobEquipmentRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"equipment_id", "started_at"},
 *     @OA\Property(
 *         property="equipment_id",
 *         description="Equipment identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="started_at",
 *         description="Started at date",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z",
 *     ),
 *     @OA\Property(
 *         property="ended_at",
 *         description="Ended at date",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z",
 *     ),
 * )
 */
class CreateJobEquipmentRequest extends ApiRequest
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
            'equipment_id' => 'required|integer|exists:equipment,id',
            'started_at'   => 'required|date_format:Y-m-d\TH:i:s\Z',
            'ended_at'     => [
                'bail',
                'date_format:Y-m-d\TH:i:s\Z',
                function ($attribute, $value, $fail) {
                    try {
                        $startedAt = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $this->get('started_at'));
                    } catch (\InvalidArgumentException $exception) {
                        return;
                    }
                    $endedAt = new Carbon($value);
                    if ($endedAt->lt($startedAt)) {
                        $fail($attribute . ' must be greater or equal than started_at');
                    }
                },
            ],
        ];
    }
}
