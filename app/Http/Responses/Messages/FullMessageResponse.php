<?php

namespace App\Http\Responses\Messages;

use App\Components\Messages\Resources\FullMessageResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullMessageResponse
 *
 * @package App\Http\Responses\Messages
 * @OA\Schema(required={"data"})
 */
class FullMessageResponse extends ApiOKResponse
{
    protected $resource = FullMessageResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullMessageResource")
     * @var \App\Components\Messages\Models\Message
     */
    protected $data;
}
