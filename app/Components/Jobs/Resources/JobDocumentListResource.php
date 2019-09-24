<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobDocumentListResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Document"),
 *     },
 * )
 *
 * @package App\Components\Jobs\Resources
 */
class JobDocumentListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="user",
     *     ref="#/components/schemas/User"
     * ),
     * @OA\Property(
     *     property="type",
     *     description="Document type",
     *     type="string",
     *     example="Invoice"
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Document description",
     *     type="string",
     *     example="Some description"
     * ),
     * @OA\Property(
     *     property="attachment_created_at",
     *     description="Time when the document was attached to the job",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *     property="attachment_updated_at",
     *     description="Time when the attachment was last modified",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
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
        $result = $this->resource->toArray();

        $result['user'] = [
            'id'         => $result['user_id'],
            'first_name' => $result['user_first_name'],
            'last_name'  => $result['user_last_name'],
            'email'      => $result['user_email'],
            'created_at' => $result['user_created_at'],
            'updated_at' => $result['user_updated_at'],
        ];
        unset($result['user_id'], $result['first_name'], $result['last_name'], $result['email']);

        if (isset($result['pivot'])) {
            $result['type']                  = $this['pivot']->getAttribute('type');
            $result['description']           = $this['pivot']->getAttribute('description');
            $result['attachment_created_at'] = $this['pivot']->getAttribute('created_at');
            $result['attachment_updated_at'] = $this['pivot']->getAttribute('updated_at');
            unset($result['pivot']);
        }

        return $result;
    }
}
