<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class CreateRunRequest
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
 *          description="Run date",
 *          type="string",
 *          format="date",
 *          example="2018-11-10"
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Name",
 *          example="Run 1"
 *     ),
 * )
 *
 * @package App\Http\Requests\Operations
 */
class CreateRunRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|integer|exists:locations,id',
            'date'        => 'required|date',
            'name'        => 'nullable|string',
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
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return Carbon::make($this->get('date'));
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }
}
