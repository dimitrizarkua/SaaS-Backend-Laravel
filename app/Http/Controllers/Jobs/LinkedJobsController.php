<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Models\Job;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Responses\Jobs\LinkedJobsListResponse;

/**
 * Class LinkedJobsController
 *
 * @package App\Http\Controllers\Jobs
 */
class LinkedJobsController extends JobsControllerBase
{
    /**
     * @OA\Get(
     *      path="/jobs/{id}/linked-jobs",
     *      tags={"Jobs"},
     *      summary="Returns list of jobs linked to specified job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LinkedJobsListResponse")
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
    public function listLinkedJobs(Job $job): ApiResponse
    {
        $this->authorize('jobs.view');

        $result = $this->service->getLinkedJobs($job->id);

        return LinkedJobsListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{id}/jobs/{linked_job_id}",
     *      tags={"Jobs"},
     *      summary="Links one job to another.",
     *      description="Allows to link one job to another.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Source job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="linked_job_id",
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
     *         response=404,
     *         description="Not found. Source job or destination job doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job has been linked earlier.",
     *      ),
     * )
     *
     * @param int $jobId
     * @param int $linkedJobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function linkJobs($jobId, $linkedJobId): ApiResponse
    {
        if ($jobId === $linkedJobId) {
            throw new NotAllowedException('Job identifiers must be different');
        }

        $this->authorize('jobs.manage_jobs');

        $this->service->linkJobs($jobId, $linkedJobId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{id}/jobs/{linked_job_id}",
     *      tags={"Jobs"},
     *      summary="Unlink two jobs.",
     *      description="Allows to unlink one job from another.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Source job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="linked_job_id",
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
     *         response=404,
     *         description="Not found. Source job or destination job doesn't exist.",
     *      )
     * )
     *
     * @param int $jobId
     * @param int $linkedJobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function unlinkJobs($jobId, $linkedJobId): ApiResponse
    {
        if ($jobId === $linkedJobId) {
            throw new NotAllowedException('Job identifiers must be different');
        }

        $this->authorize('jobs.manage_jobs');

        $this->service->unlinkJobs($jobId, $linkedJobId);

        return ApiOKResponse::make();
    }
}
