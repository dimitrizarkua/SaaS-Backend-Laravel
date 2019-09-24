<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class FinishJobEquipmentUsingRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"ended_at"},
 *     @OA\Property(
 *         property="ended_at",
 *         description="Ended at date",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z",
 *     ),
 * )
 */
class FinishJobEquipmentUsingRequest extends ApiRequest
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
            'ended_at' => 'required|date_format:Y-m-d\TH:i:s\Z',
        ];
    }

    /**
     * Returns ended at date.
     *
     * @return string
     */
    public function getEndedAt(): string
    {
        return $this->get('ended_at');
    }
}
