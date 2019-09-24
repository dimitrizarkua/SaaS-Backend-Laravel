<?php

namespace App\Components\Operations\Resources;

use OpenApi\Annotations as OA;

/**
 * Class RunTaskListResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Components\Operations\Resources\TaskListResource
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/TaskListResource")},
 * )
 */
class RunTaskListResource extends TaskListResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = parent::toArray($request);

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
