<?php

namespace App\Http\Requests\Notes;

/**
 * Class UpdateNoteRequest
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/CreateNoteRequest")},
 * )
 *
 * @package App\Http\Requests\Notes
 */
class UpdateNoteRequest extends CreateNoteRequest
{
}
