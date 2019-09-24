<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobAllowanceRequest;
use App\Http\Requests\Jobs\UpdateJobAllowanceRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Jobs\JobAllowanceResponse;
use Carbon\Carbon;
use App\Components\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobAllowancesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobAllowancesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/allowances",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns list of job allowances assigned to job.",
     *      description="Returns list of job allowances assigned to job. **`jobs.usage.view`** permission is
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
     *         @OA\JsonContent(ref="#/components/schemas/JobAllowanceResponse")
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
        $pagination = JobAllowance::where(['job_id' => $job->id])->paginate(Paginator::resolvePerPage());

        return JobAllowanceResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/allowances",
     *      tags={"Jobs", "Labours"},
     *      summary="Create new job allowances.",
     *      description="Create new job allowances. **`jobs.usage.allowances.manage`** permission is required to
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
     *              @OA\Schema(ref="#/components/schemas/CreateJobAllowanceRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobAllowanceResponse")
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
     * @param \App\Http\Requests\Jobs\CreateJobAllowanceRequest $request
     * @param \App\Components\Jobs\Models\Job                   $job
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateJobAllowanceRequest $request, Job $job)
    {
        $this->authorize('jobs.usage.allowances.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $jobAllowances                           = new JobAllowance($request->validated());
        $jobAllowances->charge_rate_per_interval = $jobAllowances->allowanceType->charge_rate_per_interval;
        $jobAllowances->saveOrFail();

        return JobAllowanceResponse::make($jobAllowances, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/allowances/{job_allowance_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns information about specific job allowance.",
     *      description="Returns information about specific job allowance. **`jobs.usage.view`** permission is
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
     *          name="job_allowances_id",
     *          in="path",
     *          required=true,
     *          description="Job allowance identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobAllowanceResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Job $job
     * @param int $jobAllowancesId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Job $job, int $jobAllowancesId)
    {
        $this->authorize('jobs.usage.view');

        return JobAllowanceResponse::make(JobAllowance::findOrFail($jobAllowancesId));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/allowances/{job_allowance_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to update specific job allowance.",
     *      description="Allows to update specific job allowance. **`jobs.usage.allowances.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_allowances_id",
     *          in="path",
     *          required=true,
     *          description="Job material identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobAllowanceRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobAllowanceResponse")
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
     * @param \App\Http\Requests\Jobs\UpdateJobAllowanceRequest $request
     * @param \App\Components\Jobs\Models\Job                   $job
     * @param int                                               $jobAllowancesId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobAllowanceRequest $request, Job $job, int $jobAllowancesId)
    {
        $this->authorize('jobs.usage.allowances.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }
        $jobAllowances = JobAllowance::findOrFail($jobAllowancesId);
        $jobAllowances->fillFromRequest($request);

        return JobAllowanceResponse::make($jobAllowances);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/allowances/{job_allowances_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Delete existing job allowances.",
     *      description="Delete existing job allowances. **`jobs.usage.allowances.manage`** permission is
     *      required to
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
     *          name="job_allowances_id",
     *          in="path",
     *          required=true,
     *          description="Job material identifier.",
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
     * @param int $jobAllowancesId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Job $job, int $jobAllowancesId)
    {
        $this->authorize('jobs.usage.allowances.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $jobAllowances = JobAllowance::findOrFail($jobAllowancesId);
        $jobAllowances->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job}/allowances/{job_allowances_id}/approve",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to approve an job allowances",
     *      description="Allows to approve an job allowances. **`jobs.usage.allowances.approve`** permission is
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
     *          name="job_allowances_id",
     *          in="path",
     *          required=true,
     *          description="Job allowances identifier.",
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
     *         description="Unable to change job allowances status.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobAllowancesId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function approve(Job $job, int $jobAllowancesId)
    {
        $this->authorize('jobs.usage.allowances.approve');
        $jobAllowances = JobAllowance::findOrFail($jobAllowancesId);
        if ($jobAllowances->approved_at) {
            return new NotAllowedResponse("Not allowed. Job allowances already approved.");
        }
        $jobAllowances->approver_id = Auth::id();
        $jobAllowances->approved_at = Carbon::now();
        $jobAllowances->saveOrFail();

        return ApiOKResponse::make();
    }
}
