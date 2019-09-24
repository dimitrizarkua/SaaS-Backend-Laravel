<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ChangeVehicleStatusRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"status_type_id"},
 *     @OA\Property(
 *          property="status_type_id",
 *          type="integer",
 *          description="Status type identifier",
 *          example=1
 *     ),
 * )
 *
 * @package App\Http\Requests\Operations
 */
class ChangeVehicleStatusRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status_type_id' => 'required|integer',
        ];
    }

    /**
     * @return int
     */
    public function getStatusTypeId(): int
    {
        return $this->get('status_type_id');
    }
}
