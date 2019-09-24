<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobStatusWorkflowInterface;
use App\Components\Jobs\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\ChangeJobStatusRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobStatusListResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobStatusesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobStatusesController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobStatusWorkflowInterface
     */
    protected $service;

    /**
     * JobsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobStatusWorkflowInterface $service
     */
    public function __construct(JobStatusWorkflowInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/next-statuses",
     *      tags={"Jobs"},
     *      summary="Get statuses that job can be transitioned to",
     *      description="Returns list of status transitions that are possible for specific job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobStatusListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\Jobs\JobStatusListResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listNextStatuses(Job $job)
    {
        $this->authorize('jobs.view');

        $statuses = $this->service->setJob($job)->getNextStatuses();

        return new JobStatusListResponse($statuses);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{id}/status",
     *      tags={"Jobs"},
     *      summary="Change job status",
     *      description="Allows to change status of the job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ChangeJobStatusRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Status could not be changed.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\ChangeJobStatusRequest $request
     * @param \App\Components\Jobs\Models\Job                $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeStatus(ChangeJobStatusRequest $request, Job $job)
    {
        $this->authorize('jobs.update');

        $this->service->setJob($job)->changeStatus($request->status, $request->note, Auth::id());

        return ApiOkResponse::make();
    }
}
