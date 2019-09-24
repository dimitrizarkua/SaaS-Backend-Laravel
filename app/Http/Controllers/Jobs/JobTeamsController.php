<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Teams\Models\Team;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Teams\TeamListResponse;

/**
 * Class JobTeamsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobTeamsController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobUsersServiceInterface
     */
    protected $service;

    /**
     * JobTeamsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobUsersServiceInterface $service
     */
    public function __construct(JobUsersServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/teams",
     *      tags={"Jobs"},
     *      summary="Returns list of teams assigned to the Job.",
     *      description="Returns list of teams assigned to the Job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TeamListResponse")
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
    public function listAssignedTeams(Job $job)
    {
        $this->authorize('jobs.view');

        return TeamListResponse::make($job->assignedTeams);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/teams/{team_id}",
     *      tags={"Jobs"},
     *      summary="Assign a team to a job.",
     *      description="Allows to assign a team to a job.",
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
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier",
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
     *         description="Not found. Either job or team doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Requested team already assigned to specified job or job is closed.",
     *      ),
     * )
     * @param Job  $job
     * @param Team $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function assignToTeam(Job $job, Team $team)
    {
        $this->authorize('jobs.assign_staff');

        $this->service->assignToTeam($job->id, $team->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/teams/{team_id}",
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
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier",
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
     *         description="Not found. Either job or team doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param Job  $job
     * @param Team $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function unassignFromTeam(Job $job, Team $team)
    {
        $this->authorize('jobs.assign_staff');

        $this->service->unassignFromTeam($job->id, $team->id);

        return ApiOKResponse::make();
    }
}
