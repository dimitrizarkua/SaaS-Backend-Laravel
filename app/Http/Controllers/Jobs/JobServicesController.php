<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\JobService;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobServiceRequest;
use App\Http\Requests\Jobs\UpdateJobServiceRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobServiceListResponse;
use App\Http\Responses\Jobs\JobServiceResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobServicesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobServicesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/services",
     *      tags={"Jobs"},
     *      summary="Get all job services",
     *      description="Returns list of all job services",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobServiceListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('jobs.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = JobService::paginate(Paginator::resolvePerPage());

        return JobServiceListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/services",
     *      tags={"Jobs"},
     *      summary="Create new job service",
     *      description="Allows to create new job service",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobServiceRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobServiceResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobServiceRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Throwable
     */
    public function store(CreateJobServiceRequest $request)
    {
        $this->authorize('jobs.update');
        $jobService = JobService::create($request->validated());
        $jobService->saveOrFail();

        return JobServiceResponse::make($jobService, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/services/{id}",
     *      tags={"Jobs"},
     *      summary="Get full info about job service",
     *      description="Retrieve full information about specific job service",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobServiceResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\JobService $service
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(JobService $service)
    {
        $this->authorize('jobs.view');

        return JobServiceResponse::make($service);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/services/{id}",
     *      tags={"Jobs"},
     *      summary="Update existing job service",
     *      description="Allows to update existing job service",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobServiceRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobServiceResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\UpdateJobServiceRequest $request
     * @param \App\Components\Jobs\Models\JobService          $service
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobServiceRequest $request, JobService $service)
    {
        $this->authorize('jobs.update');
        $service->fillFromRequest($request);

        return JobServiceResponse::make($service);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/services/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing job service",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\JobService $service
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(JobService $service)
    {
        $this->authorize('jobs.update');
        $service->delete();

        return ApiOKResponse::make();
    }
}
