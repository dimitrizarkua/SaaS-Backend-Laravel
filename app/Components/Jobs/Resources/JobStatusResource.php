<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobStatusResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobStatus
 *
 * @OA\Schema(
 *     type="object",
 *     required={"status","created_at"},
 * )
 */
class JobStatusResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="status",
     *     ref="#/components/schemas/JobStatuses"
     * ),
     * @OA\Property(
     *     property="note",
     *     description="Note",
     *     type="string",
     *     nullable=true,
     *     example="Some note",
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(
     *     property="user",
     *     type="object",
     *     required={"id","full_name"},
     *     description="User entity who updated the status",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="User Identifier",
     *          example="1",
     *     ),
     *     @OA\Property(
     *          property="full_name",
     *          type="string",
     *          example="John Smith"
     *     )
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
        $result = [
            'status'     => $this->status,
            'note'       => $this->note,
            'created_at' => $this->created_at,
        ];

        if ($this->user) {
            $result['user'] = [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
            ];
        }

        return $result;
    }
}
