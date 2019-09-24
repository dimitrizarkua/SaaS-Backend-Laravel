<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobModelChanged;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\Jobs\Services\JobsMergeService;
use App\Components\Reporting\Interfaces\CostingSummaryInterface;
use App\Http\Requests\Jobs\CreateJobRequest;
use App\Http\Requests\Jobs\SearchJobsRequest;
use App\Http\Requests\Jobs\SnoozeJobRequest;
use App\Http\Requests\Jobs\UpdateJobRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\FullJobResponse;
use App\Http\Responses\Jobs\JobCostingCountersResponse;
use App\Http\Responses\Jobs\JobCostingSummaryResponse;
use App\Http\Responses\Jobs\JobNotesAndMessagesListResponse;
use App\Http\Responses\Jobs\JobResponse;
use App\Http\Responses\Jobs\JobStatusHistoryListResponse;
use App\Http\Responses\Jobs\SearchJobResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobsController extends JobsControllerBase
{
    /**
     * @var \App\Components\Reporting\Interfaces\CostingSummaryInterface
     */
    protected $costingSummaryService;

    /**
     * JobsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobsServiceInterface         $service
     * @param \App\Components\Reporting\Interfaces\CostingSummaryInterface $costingSummaryService
     */
    public function __construct(JobsServiceInterface $service, CostingSummaryInterface $costingSummaryService)
    {
        parent::__construct($service);

        $this->costingSummaryService = $costingSummaryService;
    }

    /**
     * @OA\Post(
     *      path="/jobs",
     *      tags={"Jobs"},
     *      summary="Create new job",
     *      description="Create new job. **`jobs.create`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function store(CreateJobRequest $request)
    {
        $this->authorize('jobs.create');

        /** @var JobCreationData $jobData */
        $jobData = JobCreationData::createFromJson($request->validated());
        $job     = $this->service->createJob($jobData, JobStatuses::NEW, $request->user()->id);

        return JobResponse::make($job, null, 201);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{id}/duplicate",
     *      tags={"Jobs"},
     *      summary="Allows to duplicate existing job.",
     *      description="Allows to duplicate existing job. **`jobs.create`** permission is required to perform this
    operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function duplicate(Job $job)
    {
        $this->authorize('jobs.create');

        $jobData = (new JobCreationData($job->toArray()))->duplicate();
        $newJob  = $this->service->createJob(
            $jobData,
            JobStatuses::NEW,
            Auth::id(),
            false
        );

        return JobResponse::make($newJob, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}",
     *      tags={"Jobs"},
     *      summary="Returns full information about specific job",
     *      description="Returns full information about specific job. **`jobs.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullJobResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $jobId)
    {
        $this->authorize('jobs.view');
        $job = Job::with([
            'insurer',
            'statuses',
            'latestStatus',
            'service',
            'assignedLocation',
            'ownerLocation',
            'siteAddress',
            'followers',
        ])
            ->findOrFail($jobId);

        return FullJobResponse::make($job);
    }

    /**
     * @OA\Get(
     *      path="/jobs/search",
     *      tags={"Jobs", "Search"},
     *      summary="Search for jobs",
     *      description="Search for jobs. **`jobs.view`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Allows to search jobs by Id prefix",
     *         @OA\Schema(
     *            type="string",
     *            example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Allows to set per page count of jobs",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="include_closed",
     *          in="query",
     *          description="Allows to include closed jobs to result set",
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SearchJobResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\SearchJobsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchJobsRequest $request): ApiOKResponse
    {
        $this->authorize('jobs.view');
        $response = Job::searchForNumbers($request->getOptions(), $request->getIncludeClosed(), $request->getPerPage());

        return new SearchJobResponse($response);
    }


    /**
     * @OA\Patch(
     *      path="/jobs/{id}",
     *      tags={"Jobs"},
     *      summary="Allows to update specific job",
     *      description="Allows to update specific job. **`jobs.update`** permission is required to perform this
    operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullJobResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\UpdateJobRequest $request
     * @param \App\Components\Jobs\Models\Job          $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobRequest $request, Job $job)
    {
        $this->authorize('jobs.update');
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }
        $job = $this->service->updateJob($job, $request->validated());
        event(new JobUpdated($job, Auth::id()));
        event(new JobModelChanged($job->id, Auth::id(), $job->getChanges()));

        return FullJobResponse::make($job);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing job",
     *      description="Delete existing job. **`jobs.delete`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Job $job): ApiOKResponse
    {
        $this->authorize('jobs.delete');
        $this->service->deleteJob($job, auth()->id());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/statuses",
     *      tags={"Jobs"},
     *      summary="Returns status history of specific job",
     *      description="Returns list of statuses for specific job sorted in chronological order. **`jobs.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobStatusHistoryListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listJobStatuses(Job $job)
    {
        $this->authorize('jobs.view');

        $statuses = $job->statuses()->with('user')->get();

        return JobStatusHistoryListResponse::make($statuses);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/notes-and-messages",
     *      tags={"Jobs"},
     *      summary="Returns notes and messages for specific job",
     *      description="Returns list of notes and messages for specific job. **`jobs.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesAndMessagesListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listNotesAndMessages(Job $job)
    {
        $this->authorize('jobs.view');

        $notes    = $job->notes()
            ->with('documents', 'user', 'user.avatar')->get();
        $messages = $job->messages()
            ->with('sender', 'recipients', 'latestStatus', 'documents')->get();

        return JobNotesAndMessagesListResponse::make(['notes' => $notes, 'messages' => $messages]);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{source_job_id}/jobs/{destination_job_id}/merge",
     *      tags={"Jobs"},
     *      summary="Merge one job into another",
     *      description="Allows to merge one job into another. **`jobs.create`** permission is required to perform
    this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="source_job_id",
     *          in="path",
     *          required=true,
     *          description="Source job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="destination_job_id",
     *          in="path",
     *          required=true,
     *          description="Destination job identifier.",
     *          @OA\Schema(type="integer",example=2)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. One of the requested jobs could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param int $sourceJobId
     * @param int $destinationJobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function mergeJob(int $sourceJobId, int $destinationJobId)
    {
        $this->authorize('jobs.update');
        /** @var \App\Components\Jobs\Services\JobsMergeService $mergeService */
        $mergeService = app()->make(JobsMergeService::class);
        $mergeService->mergeJobs($sourceJobId, $destinationJobId, Auth::id());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{id}/snooze",
     *      tags={"Jobs"},
     *      summary="Snooze specific job",
     *      description="Allows to snooze specific job. **`jobs.manage_inbox`** permission is required to perform this
    operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SnoozeJobRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\SnoozeJobRequest $request
     * @param int                                      $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function snoozeJob(SnoozeJobRequest $request, int $jobId)
    {
        $this->authorize('jobs.manage_inbox');

        $this->service->snoozeJob($jobId, $request->get('snoozed_until'));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{id}/unsnooze",
     *      tags={"Jobs"},
     *      summary="Un-snooze specific job",
     *      description="Allows to un-snooze specific job. **`jobs.manage_inbox`** permission is required to perform
    this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function unsnoozeJob(int $jobId)
    {
        $this->authorize('jobs.manage_inbox');

        $this->service->unsnoozeJob($jobId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/summary",
     *      tags={"Jobs","Usage and Actuals","Reporting"},
     *      summary="Returns usage & summary report for specific job",
     *      description="Returns usage & summary report for specific job. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobCostingSummaryResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\Jobs\JobCostingSummaryResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getCostingSummary(int $jobId): JobCostingSummaryResponse
    {
        $this->authorize('jobs.usage.view');

        $summary = $this->costingSummaryService->getSummary($jobId);

        return JobCostingSummaryResponse::make($summary);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/costing-counters",
     *      tags={"Jobs","Usage and Actuals"},
     *      summary="Returns usage & actuals counters for specific job",
     *      description="Returns usage & actuals counters for specific job. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobCostingCountersResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\Jobs\JobCostingCountersResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getCostingCounters(int $jobId): JobCostingCountersResponse
    {
        $this->authorize('jobs.usage.view');

        $counters = $this->service->getJobCostingCounters($jobId);

        return JobCostingCountersResponse::make($counters);
    }
}
