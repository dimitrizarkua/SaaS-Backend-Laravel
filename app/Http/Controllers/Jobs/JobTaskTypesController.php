<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobTaskTypeRequest;
use App\Http\Requests\Jobs\UpdateJobTaskTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobTaskTypeListResponse;
use App\Http\Responses\Jobs\JobTaskTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskTypesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobTaskTypesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/tasks/types",
     *      tags={"Jobs"},
     *      summary="Returns list of all job task types",
     *      description="Allows to get a paginated list of all the job task types",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskTypeListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('management.system.settings');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = JobTaskType::paginate(Paginator::resolvePerPage());

        return JobTaskTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/tasks/types",
     *      tags={"Jobs"},
     *      summary="Create new job task type",
     *      description="Allows to create new job task type",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobTaskTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobTaskTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateJobTaskTypeRequest $request)
    {
        $this->authorize('management.system.settings');

        $jobTaskType = JobTaskType::create($request->validated());
        $jobTaskType->saveOrFail();

        return JobTaskTypeResponse::make($jobTaskType, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/tasks/types/{id}",
     *      tags={"Jobs"},
     *      summary="Retrieve information about specific job task type",
     *      description="Allows to retrieve information about specific job task type",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\JobTaskType $type
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(JobTaskType $type)
    {
        $this->authorize('management.system.settings');

        return JobTaskTypeResponse::make($type);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/tasks/types/{id}",
     *      tags={"Jobs"},
     *      summary="Update existing job task type",
     *      description="Allows to update existing job task type",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobTaskTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\UpdateJobTaskTypeRequest $request
     * @param \App\Components\Jobs\Models\JobTaskType          $type
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobTaskTypeRequest $request, JobTaskType $type)
    {
        $this->authorize('management.system.settings');

        $type->fillFromRequest($request);

        return JobTaskTypeResponse::make($type);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/tasks/types/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing job task type",
     *      description="Allows to delete existing job task type",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Requested task type has registered tasks.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\JobTaskType $type
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(JobTaskType $type)
    {
        $this->authorize('management.system.settings');

        try {
            $type->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }

        return ApiOKResponse::make();
    }
}
