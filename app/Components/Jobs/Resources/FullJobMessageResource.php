<?php

namespace App\Components\Jobs\Resources;

use App\Components\Messages\Resources\FullMessageResource;

/**
 * Class FullJobMessageResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Messages\Models\Message
 *
 * @OA\Schema(type="object")
 */
class FullJobMessageResource extends FullMessageResource
{
}
