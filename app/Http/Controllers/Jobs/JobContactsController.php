<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Contacts\Models\Contact;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\AssignContactToJobRequest;
use App\Http\Requests\Jobs\UnassignContactFromJobRequest;
use App\Http\Requests\Jobs\UpdateJobContactAssignmentRequest;
use App\Http\Responses\Jobs\AssignedContactsListResponse;
use App\Http\Responses\Jobs\ContactAssignmentTypesListResponse;

/**
 * Class JobContactsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobContactsController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobContactsServiceInterface
     */
    protected $service;

    /**
     * JobContactsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobContactsServiceInterface $service
     */
    public function __construct(JobContactsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/contacts/assignments/types",
     *      tags={"Jobs"},
     *      summary="Returns list of available contact assignment types.",
     *      description="Returns list of available contact assignment types.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactAssignmentTypesListResponse")
     *      ),
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listAssignmentTypes()
    {
        $this->authorize('jobs.view');

        $result = JobContactAssignmentType::all();

        return ContactAssignmentTypesListResponse::make($result);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/contacts/assignments",
     *      tags={"Jobs"},
     *      summary="Returns list of contacts assigned to a Job.",
     *      description="Returns list of contacts assigned to a Job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssignedContactsListResponse")
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
    public function listAssignedContacts(Job $job)
    {
        $this->authorize('jobs.view');

        $result = $this->service->getAssignedContacts($job->id);

        return AssignedContactsListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/contacts/assignments/{contact_id}",
     *      tags={"Jobs"},
     *      summary="Assign contact to specific job",
     *      description="Allows to assign a contact to a job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AssignContactToJobRequest")
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
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssignedContactsListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or contact doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job is closed or contact is already assigned to the job.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\Job         $job
     * @param \App\Components\Contacts\Models\Contact $contact
     * @param AssignContactToJobRequest               $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function assignContact(Job $job, Contact $contact, AssignContactToJobRequest $request)
    {
        $this->authorize('jobs.manage_contacts');

        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->service->assignContact(
            $job->id,
            $contact->id,
            $request->get('assignment_type_id'),
            $request->get('invoice_to') ?? false,
            $user->id
        );

        return AssignedContactsListResponse::make($this->service->getAssignedContacts($job->id));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/contacts/assignments/{contact_id}",
     *      tags={"Jobs"},
     *      summary="Update contact assignment to specific job",
     *      description="Allows to update existing contact assignment parameters for a job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobContactAssignmentRequest")
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
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssignedContactsListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or contact doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job is closed or contact is already assigned to the job.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\Job         $job
     * @param \App\Components\Contacts\Models\Contact $contact
     * @param UpdateJobContactAssignmentRequest       $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function updateAssignment(Job $job, Contact $contact, UpdateJobContactAssignmentRequest $request)
    {
        $this->authorize('jobs.manage_contacts');

        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->service->updateContactAssignment(
            $job->id,
            $contact->id,
            $request->get('assignment_type_id'),
            $request->get('invoice_to', false),
            $user->id,
            $request->get('new_assignment_type_id')
        );

        return AssignedContactsListResponse::make($this->service->getAssignedContacts($job->id));
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/contacts/assignments/{contact_id}",
     *      tags={"Jobs"},
     *      summary="Unassign contact from specific job",
     *      description="Allows to unassign a contact from a job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UnassignContactFromJobRequest")
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
     *          name="contact_id",
     *          in="path",
     *          required=true,
     *          description="Contact identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssignedContactsListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or contact doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\Job         $job
     * @param \App\Components\Contacts\Models\Contact $contact
     * @param UnassignContactFromJobRequest           $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function unassignContact(Job $job, Contact $contact, UnassignContactFromJobRequest $request)
    {
        $this->authorize('jobs.manage_contacts');

        $this->service->unassignContact($job->id, $contact->id, $request->get('assignment_type_id'));

        return AssignedContactsListResponse::make($this->service->getAssignedContacts($job->id));
    }
}
