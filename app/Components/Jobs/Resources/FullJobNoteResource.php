<?php

namespace App\Components\Jobs\Resources;

use App\Components\Notes\Resources\FullNoteResource;

/**
 * Class FullJobNoteResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Notes\Resources\FullNoteResource
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/FullNoteResource")},
 * )
 */
class FullJobNoteResource extends FullNoteResource
{
}
