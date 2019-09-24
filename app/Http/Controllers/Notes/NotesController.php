<?php

namespace App\Http\Controllers\Notes;

use App\Components\Documents\Models\Document;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\Note;
use App\Components\Notes\Models\NoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notes\CreateNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notes\FullNoteResponse;
use App\Http\Responses\Notes\NoteResponse;
use OpenApi\Annotations as OA;

/**
 * Class NotesController
 *
 * @package App\Http\Controllers\Notes
 */
class NotesController extends Controller
{
    /** @var \App\Components\Notes\Interfaces\NotesServiceInterface */
    private $service;

    /**
     * NotesController constructor.
     *
     * @param \App\Components\Notes\Interfaces\NotesServiceInterface $service
     */
    public function __construct(NotesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/notes",
     *      tags={"Notes"},
     *      summary="Allows to create new note",
     *      description="Allows to create new note",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateNoteRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NoteResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param CreateNoteRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateNoteRequest $request)
    {
        $this->authorize('notes.create');

        /** @var \App\Models\User $user */
        $user = $request->user();
        $data = new NoteData($request->getNote(), $user->id);
        $note = $this->service->addNote($data);

        return NoteResponse::make($note, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/notes/{id}",
     *      tags={"Notes"},
     *      summary="Retrieve full information about specific note",
     *      description="Retrieve full information about specific note",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullNoteResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param Note $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Note $note)
    {
        $this->authorize('notes.view');

        return FullNoteResponse::make($note);
    }

    /**
     * @OA\Patch(
     *      path="/notes/{id}",
     *      tags={"Notes"},
     *      summary="Update existing note",
     *      description="Allows to update existing note",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateNoteRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NoteResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own notes.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found. Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param UpdateNoteRequest $request
     * @param Note              $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorize('notes.update');
        $this->authorize('update', $note);

        $user = $request->user();
        $data = new NoteData($request->getNote(), $user->id);
        $note = $this->service->updateNote($data, $note->id);

        return NoteResponse::make($note);
    }

    /**
     * @OA\Delete(
     *      path="/notes/{id}",
     *      tags={"Notes"},
     *      summary="Delete existing note",
     *      description="Delete existing note",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden. One is only allowed to delete their own notes.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found. Requested resource could not be found.",
     *      ),
     * )
     * @param Note $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Note $note)
    {
        $this->authorize('notes.delete');
        $this->authorize('delete', $note);

        $note->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/notes/{note_id}/documents/{document_id}",
     *      tags={"Notes","Documents"},
     *      summary="Attach document to specific note",
     *      description="Allows to attach a document to specific note",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="document_id",
     *          in="path",
     *          required=true,
     *          description="Document identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own notes.",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either note or document doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Document has been already attached earlier to this note.",
     *      ),
     * )
     *
     * @param Note     $note
     * @param Document $document
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function attachDocument(Note $note, Document $document)
    {
        $this->authorize('notes.update');
        $this->authorize('update', $note);

        $this->service->attachDocument($document, $note->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/notes/{note_id}/documents/{document_id}",
     *      tags={"Notes","Documents"},
     *      summary="Detach document from specific note",
     *      description="Allows detach a document from specific note",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="document_id",
     *          in="path",
     *          required=true,
     *          description="Document identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own notes.",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either note or document doesn't exist.",
     *      ),
     * )
     *
     * @param Note     $note
     * @param Document $document
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachDocument(Note $note, Document $document)
    {
        $this->authorize('notes.update');
        $this->authorize('update', $note);

        $this->service->detachDocument($document, $note->id);

        return ApiOKResponse::make();
    }
}
