<?php

namespace App\Http\Requests\Notes;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateNoteRequest
 *
 * @package App\Http\Requests\Notes
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="note",
 *          description="Note text",
 *          type="string",
 *          example="Some text",
 *     )
 * )
 */
class CreateNoteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'note' => 'required|string',
        ];
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->get('note');
    }
}
