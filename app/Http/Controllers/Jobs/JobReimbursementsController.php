<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobReimbursement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobReimbursementRequest;
use App\Http\Requests\Jobs\UpdateJobReimbursementRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Jobs\JobReimbursementResponse;
use Carbon\Carbon;
use App\Components\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobReimbursementsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobReimbursementsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/reimbursements",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns list job reimbursement assigned to job.",
     *      description="Returns list job reimbursement assigned to job. **`jobs.usage.view`** permission is
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
     *         @OA\JsonContent(ref="#/components/schemas/JobReimbursementResponse")
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
        $pagination = JobReimbursement::where(['job_id' => $job->id])->paginate(Paginator::resolvePerPage());

        return JobReimbursementResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/reimbursements",
     *      tags={"Jobs", "Labours"},
     *      summary="Create new job reimbursement.",
     *      description="Create new job reimbursement. **`jobs.usage.reimbursements.manage`** permission is required to
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
     *              @OA\Schema(ref="#/components/schemas/CreateJobReimbursementRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobReimbursementResponse")
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
     * @param \App\Http\Requests\Jobs\CreateJobReimbursementRequest $request
     * @param \App\Components\Jobs\Models\Job                       $job
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateJobReimbursementRequest $request, Job $job)
    {
        $this->authorize('jobs.usage.reimbursements.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $jobReimbursements         = new JobReimbursement($request->validated());
        $jobReimbursements->job_id = $job->id;
        $jobReimbursements->saveOrFail();

        return JobReimbursementResponse::make($jobReimbursements, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/reimbursements/{job_reimbursements_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Returns information about specific job reimbursement.",
     *      description="Returns information about specific job reimbursement. **`jobs.usage.view`** permission is
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
     *          name="job_reimbursements_id",
     *          in="path",
     *          required=true,
     *          description="Job reimbursement identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobReimbursementResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Job $job
     * @param int $jobReimbursementsId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Job $job, int $jobReimbursementsId)
    {
        $this->authorize('jobs.usage.view');

        return JobReimbursementResponse::make(JobReimbursement::findOrFail($jobReimbursementsId));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/reimbursements/{job_reimbursements_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to update specific job reimbursement.",
     *      description="Allows to update specific job reimbursement. **`jobs.usage.reimbursements.manage`** permission
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
     *          name="job_reimbursements_id",
     *          in="path",
     *          required=true,
     *          description="Job reimbursement identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobReimbursementRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobReimbursementResponse")
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
     * @param \App\Http\Requests\Jobs\UpdateJobReimbursementRequest $request
     * @param \App\Components\Jobs\Models\Job                       $job
     * @param int                                                   $jobReimbursementsId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobReimbursementRequest $request, Job $job, int $jobReimbursementsId)
    {
        $this->authorize('jobs.usage.reimbursements.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }
        $jobReimbursements = JobReimbursement::findOrFail($jobReimbursementsId);
        $jobReimbursements->fillFromRequest($request);

        return JobReimbursementResponse::make($jobReimbursements);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/reimbursements/{job_reimbursements_id}",
     *      tags={"Jobs", "Labours"},
     *      summary="Delete existing job reimbursement.",
     *      description="Delete existing job reimbursement. **`jobs.usage.reimbursements.manage`** permission is
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
     *          name="job_reimbursements_id",
     *          in="path",
     *          required=true,
     *          description="Job reimbursement identifier.",
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
     * @param int $jobReimbursementsId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Job $job, int $jobReimbursementsId)
    {
        $this->authorize('jobs.usage.reimbursements.manage');

        if ($job->isClosed()) {
            return new NotAllowedResponse("Not allowed. Could not make changes to the closed or cancelled job.");
        }

        $jobReimbursements = JobReimbursement::findOrFail($jobReimbursementsId);
        $jobReimbursements->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job}/reimbursements/{job_reimbursements_id}/approve",
     *      tags={"Jobs", "Labours"},
     *      summary="Allows to approve an job reimbursement",
     *      description="Allows to approve an job reimbursement. **`jobs.usage.reimbursements.approve`** permission is
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
     *          name="job_reimbursements_id",
     *          in="path",
     *          required=true,
     *          description="Job reimbursements identifier.",
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
     *         description="Unable to change job reimbursement status.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobReimbursementsId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Error\NotAllowedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function approve(Job $job, int $jobReimbursementsId)
    {
        $this->authorize('jobs.usage.reimbursements.approve');
        $jobReimbursements = JobReimbursement::findOrFail($jobReimbursementsId);
        if ($jobReimbursements->approved_at) {
            return new NotAllowedResponse("Not allowed. Job reimbursements already approved.");
        }
        $jobReimbursements->approver_id = Auth::id();
        $jobReimbursements->approved_at = Carbon::now();
        $jobReimbursements->saveOrFail();

        return ApiOKResponse::make();
    }
}
