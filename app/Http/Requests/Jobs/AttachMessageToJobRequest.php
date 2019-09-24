<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class AttachMessageToJobRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="send_immediately",
 *          description="Allows to immediately forward message for delivery to recipient.",
 *          type="boolean",
 *          default=true,
 *     ),
 * )
 *
 * @package App\Http\Requests\Jobs
 */
class AttachMessageToJobRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'send_immediately' => 'boolean',
        ];
    }
}
