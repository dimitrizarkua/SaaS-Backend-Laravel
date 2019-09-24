<?php

namespace App\Http\Requests\Contacts;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateContactNoteRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="meeting_id",
 *          description="Meeting id",
 *          type="integer",
 *          example=1,
 *     )
 * )
 *
 * @package App\Http\Requests\Contacts
 */
class CreateContactNoteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'meeting_id' => 'int|exists:meetings,id',
        ];
    }
}
