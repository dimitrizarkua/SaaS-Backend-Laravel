<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobDocumentsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Responses\Jobs\JobDocumentsListResponse;

/**
 * Class JobDocumentsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobDocumentsController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobDocumentsServiceInterface
     */
    protected $service;

    /**
     * JobContactsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobDocumentsServiceInterface $service
     */
    public function __construct(JobDocumentsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/documents",
     *      tags={"Jobs"},
     *      summary="Get job documents.",
     *      description="Get list of job documents.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobDocumentsListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Job could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\Contacts\ContactListResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listJobDocuments(Job $job)
    {
        $this->authorize('jobs.view');

        $query = $job->documents()
            ->join(
                'users',
                'job_documents.creator_id',
                '=',
                'users.id'
            )
            ->select([
                'documents.id',
                'documents.storage_uid',
                'documents.file_name',
                'documents.file_size',
                'documents.file_hash',
                'documents.mime_type',
                'documents.created_at',
                'documents.updated_at',
                'job_documents.type',
                'job_documents.description',
                'job_documents.created_at as attachment_created_at',
                'job_documents.updated_at as attachment_updated_at',
                'users.id as user_id',
                'users.email as user_email',
                'users.first_name as user_first_name',
                'users.last_name as user_last_name',
                'users.created_at as user_created_at',
                'users.updated_at as user_updated_at',
            ]);

        return JobDocumentsListResponse::make($query->get());
    }
}
