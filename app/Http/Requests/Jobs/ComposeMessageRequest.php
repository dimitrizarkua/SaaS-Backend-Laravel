<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ComposeMessageRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"template_id"},
 *     @OA\Property(
 *         property="template_id",
 *         description="Template identifier",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class ComposeMessageRequest extends ApiRequest
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
            'template_id' => 'required|integer',
        ];
    }

    /**
     * @return int
     */
    public function getTemplateId(): int
    {
        return $this->get('template_id');
    }
}
