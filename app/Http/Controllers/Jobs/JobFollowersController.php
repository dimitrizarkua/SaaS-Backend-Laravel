<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class JobFollowersController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobFollowersController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobUsersServiceInterface
     */
    protected $service;

    /**
     * JobFollowersController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobUsersServiceInterface $service
     */
    public function __construct(JobUsersServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/jobs/{id}/follow",
     *      tags={"Jobs"},
     *      summary="Follow a job",
     *      description="Makes currently authenticated user a job follower.",
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
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job is closed or user already follows specified job.",
     *      ),
     * )
     * @param Job     $job
     * @param Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function followJob(Job $job, Request $request)
    {
        $this->authorize('jobs.view');

        /** @var User $user */
        $user = $request->user();

        $this->service->follow($job->id, $user->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{id}/follow",
     *      tags={"Jobs"},
     *      summary="Unfollow a job",
     *      description="Removes currently authenticated user from job followers.",
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
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param Job     $job
     * @param Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function unfollowJob(Job $job, Request $request)
    {
        $this->authorize('jobs.view');

        /** @var User $user */
        $user = $request->user();

        $this->service->unfollow($job->id, $user->id);

        return ApiOKResponse::make();
    }
}
