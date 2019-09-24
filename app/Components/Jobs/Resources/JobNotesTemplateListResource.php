<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class JobNotesTemplateListResource
 *
 * @package App\Components\Jobs\Resources
 *
 * @OA\Schema(type="object")
 */
class JobNotesTemplateListResource extends JsonResource
{
    /**
     * @OA\Property(property="id", type="integer", description="Template identifier", example=1),
     * @OA\Property(property="name", type="string", description="Template name", example="Job Scheduled"),
     * @OA\Property(
     *     property="active",
     *     type="boolean",
     *     description="Indicates if the template is active",
     *     example="true"
     * ),
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
        $result = $this->resource;

        unset($result['body']);

        return $result;
    }
}
