<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Users\UserListResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobUsersController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobUsersController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobUsersServiceInterface
     */
    protected $service;

    /**
     * JobUsersController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobUsersServiceInterface $service
     */
    public function __construct(JobUsersServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/users",
     *      tags={"Jobs"},
     *      summary="Returns list of users assigned to the Job.",
     *      description="Returns list of users assigned to the Job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserListResponse")
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
    public function listAssignedUsers(Job $job)
    {
        $this->authorize('jobs.view');

        return UserListResponse::make($job->assignedUsers);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/users/{user_id}",
     *      tags={"Jobs"},
     *      summary="Assign a user to a job.",
     *      description="Allows to assign a user to a job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or user doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Requested user already assigned to specified job or job is closed.",
     *      ),
     * )
     * @param Job  $job
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function assignToUser(Job $job, User $user)
    {
        $this->authorize('jobs.assign_staff');

        $this->service->assignToUser($job->id, $user->id, Auth::id());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/users/{user_id}",
     *      tags={"Jobs"},
     *      summary="Allows to unassign a user from a Job.",
     *      description="Allows to unassign a user from a Job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or user doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param Job  $job
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function unassignFromUser(Job $job, User $user)
    {
        $this->authorize('jobs.assign_staff');

        $this->service->unassignFromUser($job->id, $user->id);

        return ApiOKResponse::make();
    }
}
