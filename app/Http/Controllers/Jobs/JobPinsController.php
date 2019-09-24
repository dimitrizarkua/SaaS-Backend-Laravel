<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\Job;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobPinsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobPinsController extends JobsControllerBase
{
    /**
     * @OA\Post(
     *      path="/jobs/{id}/pin",
     *      tags={"Jobs"},
     *      summary="Allows to pin the job.",
     *      description="Allows to pin the job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
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
    public function pinJob(Job $job)
    {
        $this->authorize('jobs.manage_inbox');

        $this->service->pin($job->id, true);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{id}/pin",
     *      tags={"Jobs"},
     *      summary="Allows to unpin the job.",
     *      description="Allows to unpin the job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
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
    public function unpinJob(Job $job)
    {
        $this->authorize('jobs.manage_inbox');

        $this->service->pin($job->id, false);

        return ApiOKResponse::make();
    }
}
