<?php

namespace App\Http\Requests\Teams;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateTeamRequest
 *
 * @package App\Http\Requests\Teams
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         description="Team name",
 *         type="string",
 *         example="Dream team"
 *     )
 * )
 */
class UpdateTeamRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:teams',
        ];
    }
}
