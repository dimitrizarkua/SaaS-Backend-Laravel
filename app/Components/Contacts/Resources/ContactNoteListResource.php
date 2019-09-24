<?php

namespace App\Components\Contacts\Resources;

use App\Components\Notes\Resources\FullNoteResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactNoteListResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/FullNoteResource"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ContactNoteListResource extends FullNoteResource
{
    /**
     * @OA\Property(
     *     property="attached_at",
     *     description="Time when the note has been attached",
     *     type="string",
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="meeting",
     *     type="object",
     *     nullable=true,
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Meeting identifier",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Meeting title",
     *         example="Weekly meeting"
     *     ),
     *     @OA\Property(property="scheduled_at", type="string", format="date-time")
     * ),
     * @OA\Property(
     *     property="user",
     *     type="object",
     *     @OA\Property(
     *         property="first_name",
     *         type="string",
     *         example="John"
     *     ),
     *     @OA\Property(
     *         property="last_name",
     *         type="string",
     *         example="Smith"
     *     ),
     *     @OA\Property(
     *         property="full_name",
     *         type="string",
     *         example="John Smith"
     *     ),
     *     @OA\Property(
     *         property="avatar_url",
     *         description="Avatar image URL",
     *         type="string",
     *         example="http://avatar-image-url",
     *         nullable=true
     *     ),
     * ),
     * @OA\Property(
     *     property="contact",
     *     type="object",
     *     @OA\Property(
     *         property="id",
     *         description="Contact identifier",
     *         type="integer",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         description="Contact name",
     *         type="string",
     *         example="Kylie Taylor"
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
        $result            = parent::toArray($request);
        $result['user']    = $this->resource->user->only(['first_name', 'last_name', 'full_name', 'avatar_url']);
        $result['meeting'] = null;
        $result['contact'] = null;

        if (isset($this->pivot)) {
            /** @var \App\Components\Contacts\Models\Contact $contact */
            $contact = $this->pivot->contact_id
                ? $this->resource->contacts()->findOrFail($this->pivot->contact_id)
                : null;

            /** @var \App\Components\Meetings\Models\Meeting $meeting */
            $meeting = $this->pivot->meeting_id
                ? $this->resource->meetings()->findOrFail($this->pivot->meeting_id)
                : null;

            if (null !== $meeting) {
                $result['meeting'] = [
                    'id'           => $meeting->id,
                    'scheduled_at' => $meeting->scheduled_at,
                    'title'        => $meeting->title,
                ];
            }

            if (null !== $contact) {
                $result['contact'] = [
                    'id'   => $contact->id,
                    'name' => $contact->getContactName(),
                ];
            }

            $result['attached_at'] = $this->pivot->created_at;

            unset($result['pivot']);
        }

        unset($result['contacts']);
        unset($result['meetings']);

        return $result;
    }
}
