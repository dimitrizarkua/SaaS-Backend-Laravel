<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Notes\Models\Note;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\AttachNoteToJobRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobNotesListResponse;

/**
 * Class JobNotesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobNotesController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobNotesServiceInterface
     */
    protected $service;

    /**
     * JobNotesController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobNotesServiceInterface $service
     */
    public function __construct(JobNotesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/notes",
     *      tags={"Jobs"},
     *      summary="Returns list of notes added to a Job.",
     *      description="Returns list of notes added to a Job in reverse chronological order.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listNotes(Job $job)
    {
        $this->authorize('jobs.view');

        $result = $job->notes()->with('documents', 'user', 'user.avatar')->get();

        return JobNotesListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/notes/{note_id}",
     *      tags={"Jobs"},
     *      summary="Add a note to specific job",
     *      description="Allows to add a note to a job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachNoteToJobRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or note doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Either note is already attached
    to this job or job status may not be changes for some reason, or job is closed.",
     *      ),
     * )
     *
     * @param Job                    $job
     * @param Note                   $note
     * @param AttachNoteToJobRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function addNote(Job $job, Note $note, AttachNoteToJobRequest $request)
    {
        $this->authorize('jobs.manage_notes');
        $this->authorize('attach', $note);

        $this->service->addNote($job->id, $note->id, $request->post('new_status'));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/notes/{note_id}",
     *      tags={"Jobs"},
     *      summary="Remove a note from specific job",
     *      description="Allows to remove a note from a job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or note doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     *
     * @param Job  $job
     * @param Note $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function deleteNote(Job $job, Note $note)
    {
        $this->authorize('jobs.manage_notes');
        $this->authorize('detach', $note);

        $this->service->removeNote($job->id, $note->id);

        return ApiOKResponse::make();
    }
}
