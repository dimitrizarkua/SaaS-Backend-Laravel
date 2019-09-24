<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class CreateRunsFromTemplateRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"date"},
 *     @OA\Property(
 *          property="date",
 *          description="Run date",
 *          type="string",
 *          format="date",
 *          example="2018-11-10"
 *     ),
 * )
 *
 * @package App\Http\Requests\Operations
 */
class CreateRunsFromTemplateRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
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
