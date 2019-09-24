<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactTagListResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactTagsController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactTagsController extends ContactsControllerBase
{
    /**
     * @OA\Get(
     *      path="/contacts/{contact_id}/tags",
     *      tags={"Contacts"},
     *      summary="Returns list of all contact tags",
     *      description="Returns list of all contact tags.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactNoteListResponse")
     *      ),
     * )
     * @param \App\Components\Contacts\Models\Contact $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getContactTags(Contact $contact)
    {
        $this->authorize('contacts.view');

        return ContactTagListResponse::make($contact->tags);
    }

    /**
     * @OA\Post(
     *      path="/contacts/{contact_id}/tags/{tag_id}",
     *      tags={"Contacts"},
     *      summary="Add contact tag",
     *      description="Allows to add contact tag.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="Tag identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Tag already added to this contact",
     *      ),
     * )
     * @param int $contactId
     * @param int $tagId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addContactTag(int $contactId, int $tagId)
    {
        $this->authorize('contacts.update');
        $this->service->addTag($contactId, $tagId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{contact_id}/tags/{tag_id}",
     *      tags={"Contacts"},
     *      summary="Delete contact tag",
     *      description="Allows to delete contact tag.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="Tag identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     * )
     * @param int $contactId
     * @param int $tagId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteContactTag(int $contactId, int $tagId)
    {
        $this->authorize('contacts.update');
        $this->service->deleteTag($contactId, $tagId);

        return ApiOKResponse::make();
    }
}
