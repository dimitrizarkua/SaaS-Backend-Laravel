<?php

namespace App\Http\Requests\Management;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SeederRequest
 *
 * @package App\Http\Requests\Management
 *
 * @property-read $class
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="class",
 *         description="Class name of seeder",
 *         type="string",
 *         example="AccountTypesSeeder"
 *     ),
 * )
 */
class SeederRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'class' => 'required|string'
        ];
    }
}
