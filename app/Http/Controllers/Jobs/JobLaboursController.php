<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobLabourServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\VO\JobLabourData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobLabourRequest;
use App\Http\Requests\Jobs\UpdateJobLabourRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobLabourResponse;
use App\Http\Responses\Jobs\JobLabourTotalAmountResponse;
use Illuminate\Support\Facades\Auth;
use App\Components\Pagination\Paginator;

/**
 * Class JobLaboursController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobLaboursController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobLabourServiceInterface
     */
    protected $service;

    /**
     * JobLaboursController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobLabourServiceInterface $service
     */
    public function __construct(JobLabourServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/labours",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns list of job labours assigned to job.",
     *      description="Returns list of job labours assigned to job. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLabourResponse")
     *      ),
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
     */
    public function index(Job $job)
    {
        $this->authorize('jobs.usage.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = JobLabour::where(['job_id' => $job->id])->paginate(Paginator::resolvePerPage());

        return JobLabourResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/labours",
     *      tags={"Jobs", "Labours"},
     *      summary="Create new job labour.",
     *      description="Create new job labour. **`jobs.usage.labour.create`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobLabourRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLabourResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobLabourRequest $request
     * @param \App\Components\Jobs\Models\Job                $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function store(CreateJobLabourRequest $request, Job $job)
    {
        $this->authorize('jobs.usage.labour.create');

        $data           = $request->validated();
        $data['job_id'] = $job->id;
        $jobLabour      = $this->service->createJobLabour(new JobLabourData($data));

        return JobLabourResponse::make($jobLabour, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/labours/{job_labour_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns information about specific job labour.",
     *      description="Returns information about specific job labour. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_labour_id",
     *          in="path",
     *          required=true,
     *          description="Job labour identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLabourResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobLabourId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Job $job, int $jobLabourId)
    {
        $this->authorize('jobs.usage.view');

        return JobLabourResponse::make(JobLabour::findOrFail($jobLabourId));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/labours/{job_labour_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to update specific job labour.",
     *      description="Allows to update specific job labour. **`jobs.usage.labour.update`** permission is
    required to perform this operation. In order to manage labours entries that were not created
    by the current user the additionally **jobs.usage.labour.manage** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_labour_id",
     *          in="path",
     *          required=true,
     *          description="Job labour identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobLabourRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLabourResponse")
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
     * @param \App\Http\Requests\Jobs\UpdateJobLabourRequest $request
     * @param \App\Components\Jobs\Models\Job                $job
     * @param int                                            $jobLabourId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function update(UpdateJobLabourRequest $request, Job $job, int $jobLabourId)
    {
        $this->authorize('jobs.usage.labour.update');
        $jobLabour = JobLabour::findOrFail($jobLabourId);
        if ($jobLabour->creator_id !== Auth::id()) {
            $this->authorize('jobs.usage.labour.manage');
        }

        /** @var JobLabourData $data */
        $data      = new JobLabourData($request->validated());
        $jobLabour = $this->service->updateJobLabour($jobLabour, $data);

        return JobLabourResponse::make($jobLabour);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/labours/{job_labour_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Delete existing job labour.",
     *      description="Delete existing job labour. **`jobs.usage.labour.delete`** permission is required to
    perform this operation. In order to manage labours entries that were not created
    by the current user the additionally **jobs.usage.labour.manage** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_labour_id",
     *          in="path",
     *          required=true,
     *          description="Job labour identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
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
     * @param int                             $jobLabourId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(Job $job, int $jobLabourId)
    {
        $this->authorize('jobs.usage.labour.delete');

        $jobLabour = JobLabour::findOrFail($jobLabourId);
        if ($jobLabour->creator_id !== Auth::id()) {
            $this->authorize('jobs.usage.labour.manage');
        }

        $this->service->deleteJobLabour($jobLabour);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/labours/amount",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns total amount of job labours assigned to job.",
     *      description="Returns total amount of job labours assigned to job. **`jobs.usage.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLabourTotalAmountResponse")
     *      ),
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
     * @throws \Throwable
     */
    public function getTotalAmount(Job $job)
    {
        $this->authorize('jobs.usage.view');

        $amount = $this->service->calculateTotalAmountByJob($job->id);

        return JobLabourTotalAmountResponse::make($amount);
    }
}
