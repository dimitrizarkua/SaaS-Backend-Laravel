<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobLahaCompensationRequest;
use App\Http\Requests\Jobs\UpdateJobLahaCompensationRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Jobs\JobLahaCompensationResponse;
use Carbon\Carbon;
use App\Components\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobLahaCompensationsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobLahaCompensationsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/laha",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns list job laha compensations assigned to job.",
     *      description="Returns list job laha compensations assigned to job. **`jobs.usage.view`** permission is
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
     *         @OA\JsonContent(ref="#/components/schemas/JobLahaCompensationResponse")
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
        $pagination = JobLahaCompensation::where(['job_id' => $job->id])->paginate(Paginator::resolvePerPage());

        return JobLahaCompensationResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/laha",
     *      tags={"Jobs", "Labours"},
     *      summary="Create new job laha compensations.",
     *      description="Create new job laha compensations. **`jobs.usage.laha.manage`** permission is required to
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
     *              @OA\Schema(ref="#/components/schemas/CreateJobLahaCompensationRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLahaCompensationResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobLahaCompensationRequest $request
     * @param \App\Components\Jobs\Models\Job                          $job
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateJobLahaCompensationRequest $request, Job $job)
    {
        $this->authorize('jobs.usage.laha.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $jobLahaCompensation               = new JobLahaCompensation($request->validated());
        $jobLahaCompensation->rate_per_day = $jobLahaCompensation->lahaCompensation->rate_per_day;
        $jobLahaCompensation->saveOrFail();

        return JobLahaCompensationResponse::make($jobLahaCompensation, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/laha/{job_laha_compensation_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns information about specific job laha compensation.",
     *      description="Returns information about specific job laha compenstaion. **`jobs.usage.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_laha_compensation_id",
     *          in="path",
     *          required=true,
     *          description="Job laha compensation identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLahaCompensationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Job $job
     * @param int $lahaCompensationId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Job $job, int $lahaCompensationId)
    {
        $this->authorize('jobs.usage.view');

        return JobLahaCompensationResponse::make(JobLahaCompensation::findOrFail($lahaCompensationId));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/laha/{job_laha_compensation_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to update specific job laha compensation.",
     *      description="Allows to update specific job laha compensation. **`jobs.usage.laha.manage`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_laha_compensation_id",
     *          in="path",
     *          required=true,
     *          description="Job laha compensation identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobLahaCompensationRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobLahaCompensationResponse")
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
     * @param \App\Http\Requests\Jobs\UpdateJobLahaCompensationRequest $request
     * @param \App\Components\Jobs\Models\Job                          $job
     * @param int                                                      $lahaCompensationId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobLahaCompensationRequest $request, Job $job, int $lahaCompensationId)
    {
        $this->authorize('jobs.usage.laha.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }
        $lahaCompensation = JobLahaCompensation::findOrFail($lahaCompensationId);
        $lahaCompensation->fillFromRequest($request);

        return JobLahaCompensationResponse::make($lahaCompensation);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/laha/{job_laha_compensation_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Delete existing job laha compensation.",
     *      description="Delete existing job laha compensation. **`jobs.usage.laha.manage`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_laha_compensation_id",
     *          in="path",
     *          required=true,
     *          description="Job laha compensation identifier.",
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
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param Job $job
     * @param int $lahaCompensationId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Job $job, int $lahaCompensationId)
    {
        $this->authorize('jobs.usage.laha.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $lahaCompensation = JobLahaCompensation::findOrFail($lahaCompensationId);
        $lahaCompensation->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job}/laha/{job_laha_compensation_id}/approve",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to approve an job laha compensation",
     *      description="Allows to approve an job laha compensation. **`jobs.usage.laha.approve`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_laha_compensation_id",
     *          in="path",
     *          required=true,
     *          description="Job laha compensation identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
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
     *         description="Unable to change job reimbursements status.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobLahaCompensationId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function approve(Job $job, int $jobLahaCompensationId)
    {
        $this->authorize('jobs.usage.laha.approve');
        $jobLahaCompensation = JobLahaCompensation::findOrFail($jobLahaCompensationId);
        if ($jobLahaCompensation->approved_at) {
            return new NotAllowedResponse("Not allowed. Job laha compensation already approved.");
        }
        $jobLahaCompensation->approver_id = Auth::id();
        $jobLahaCompensation->approved_at = Carbon::now();
        $jobLahaCompensation->saveOrFail();

        return ApiOKResponse::make();
    }
}
