<?php

namespace App\Components\Messages\Resources;

use App\Components\Documents\Resources\DocumentResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FullMessageResource
 *
 * @package App\Components\Messages\Resources
 * @mixin \App\Components\Messages\Models\Message
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Message")},
 * )
 */
class FullMessageResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="sender",
     *      description="Message sender",
     *      ref="#/components/schemas/User"
     * )
     * @OA\Property(
     *      property="recipients",
     *      type="array",
     *      description="Message recipients",
     *      @OA\Items(ref="#/components/schemas/MessageRecipient"),
     * )
     * @OA\Property(
     *      property="documents",
     *      type="array",
     *      description="Attached documents",
     *      @OA\Items(ref="#/components/schemas/DocumentResource"),
     * )
     * @OA\Property(
     *      property="latestStatus",
     *      description="Latest (or current) status of the message",
     *      ref="#/components/schemas/MessageStatus"
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['sender'] = $this->resource->sender;
        $result['recipients'] = $this->resource->recipients;
        $result['documents'] = DocumentResource::collection($this->resource->documents);
        $result['latest_status'] = $this->resource->latestStatus;

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
