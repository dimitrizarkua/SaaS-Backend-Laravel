<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\Note;
use App\Http\Requests\Contacts\CreateContactNoteRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactNoteListResponse;
use App\Http\Responses\Contacts\ContactNoteResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactNotesController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactNotesController extends ContactsControllerBase
{
    /**
     * @OA\Get(
     *      path="/contacts/{contact_id}/notes",
     *      tags={"Contacts"},
     *      summary="Returns list of all contact notes",
     *      description="Returns list of all contact notes.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactNoteListResponse")
     *      ),
     * )
     * @param \App\Components\Contacts\Models\Contact $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getContactNotes(Contact $contact)
    {
        $this->authorize('contacts.view');

        $result = $contact->notes()->with(
            'documents',
            'user',
            'user.avatar',
            'contacts',
            'meetings',
        )->get();

        return ContactNoteListResponse::make($result);
    }

    /**
     * @OA\Get(
     *      path="/contacts/{contact_id}/notes/{note_id}",
     *      tags={"Contacts"},
     *      summary="Get detailed note information",
     *      description="Allows to retrieve detailed information about specific contact note.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactNoteResponse")
     *      ),
     * )
     * @param \App\Components\Contacts\Models\Contact $contact
     * @param \App\Components\Notes\Models\Note       $note
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function viewContactNote(Contact $contact, Note $note)
    {
        $this->authorize('contacts.view');

        return ContactNoteResponse::make($note);
    }

    /**
     * @OA\Post(
     *      path="/contacts/{contact_id}/notes/{note_id}",
     *      tags={"Contacts"},
     *      summary="Allows to add contact note",
     *      description="Allows to add new contact note.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateContactNoteRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK"
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Note already added to this contact",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\CreateContactNoteRequest $request
     * @param int                                                  $contactId
     * @param int                                                  $noteId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addContactNote(CreateContactNoteRequest $request, int $contactId, int $noteId)
    {
        $this->authorize('contacts.update');
        $this->service->addNote($contactId, $noteId, $request->get('meeting_id'));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{contact_id}/notes/{note_id}",
     *      tags={"Contacts"},
     *      summary="Allows to remove a contact note",
     *      description="Allows to remove a contact note.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(
     *              type="integer",
     *             example=1,
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param int                                                  $contactId
     * @param int                                                  $noteId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteContactNote(int $contactId, int $noteId)
    {
        $this->authorize('contacts.update');

        /** @var NotesServiceInterface $notesService */
        $notesService = app()->make(NotesServiceInterface::class);
        $this->authorize('detach', $notesService->getNote($noteId));

        $this->service->deleteNote($contactId, $noteId);

        return ApiOKResponse::make();
    }
}
