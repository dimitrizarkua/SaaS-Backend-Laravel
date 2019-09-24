<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobPhotosListResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Photos\Resources\PhotoResource
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/PhotoResource")},
 * )
 */
class JobPhotosListResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="creator",
     *      description="User who has attached the photo",
     *      ref="#/components/schemas/User",
     * )
     * @OA\Property(
     *      property="modified_by",
     *      description="User who has last modified the description",
     *      ref="#/components/schemas/User",
     * )
     * @OA\Property(
     *      property="description",
     *      type="string",
     *      description="Photo description",
     * )
     * @OA\Property(
     *      property="attached_at",
     *      type="string",
     *      format="date-time",
     *      description="Date and time when the photo was attached to the job"
     * )
     * @OA\Property(
     *      property="description_updated_at",
     *      type="string",
     *      format="date-time",
     *      description="Date and time when the description was last modified"
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
        $data = $this->resource->toArray();

        return array_merge($data['photo'], [
            'description'            => $data['description'],
            'attached_at'            => $data['created_at'],
            'description_updated_at' => $data['updated_at'],
            'creator'                => $data['creator'],
            'modified_by'            => $data['modified_by'],
        ]);
    }
}
