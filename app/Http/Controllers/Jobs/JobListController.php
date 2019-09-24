<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Teams\Models\Team;
use App\Http\Controllers\Controller;
use App\Http\Responses\Jobs\JobsInfoResponse;
use App\Http\Responses\Jobs\JobListResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * Class JobListController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobListController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobListingServiceInterface
     */
    private $service;

    /**
     * JobListController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobListingServiceInterface $service
     */
    public function __construct(JobListingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/info",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns jobs counters for different categories.",
     *      description="Returns jobs counters for different categories: Inbox, Mine, All active jobs, Teams, Closed,
    No Contact 24 Hours. `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobsInfoResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function info()
    {
        $this->authorize('jobs.view');
        $info = $this->service->getCountersAndTeams(Auth::id());

        return JobsInfoResponse::make($info);
    }

    /**
     * @OA\Get(
     *      path="/jobs/inbox",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of unassigned and pinned job.",
     *      description="Returns list of unassigned and pinned job.
    `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function inbox()
    {
        $this->authorize('jobs.view');
        $jobList = $this->service->getInbox();

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/local",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of jobs by an authenticated user's locations.",
     *      description="Returns jobs whose assigned or owner locations match a location of an authenticated user.
    `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function local()
    {
        $this->authorize('jobs.view');
        $jobList = $this->service->getLocal(Auth::id());

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of jobs assigned to authenticated user or their teams.",
     *      description="Returns list of jobs assigned to authenticated user or their teams.
    `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mine()
    {
        $this->authorize('jobs.view');
        $jobList = $this->service->getMine(Auth::id());

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine/active",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of all jobs which are assigned to a authenticated user or their teams and with active
    statuses.",
     *      description="Returns list of all jobs which are assigned to a authenticated user or their teams
    and with active statuses. `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mineActive()
    {
        $this->authorize('jobs.view');
        $jobList = $this->service->getActive(Auth::id());

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine/closed",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of all jobs which are assigned to a authenticated user or their teams and with closed
    status.",
     *      description="Returns list of all jobs which are assigned to a authenticated user or their teams
    and with closed status. `jobs.view` permission is required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mineClosed()
    {
        $this->authorize('jobs.view');
        $jobList = $this->service->getClosed(Auth::id());

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine/teams/{team_id}",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of jobs assigned to a team which authenticated user is member of.",
     *      description="Returns list of jobs assigned to a team which authenticated user is member of.
    `jobs.view` permission is required to perform this operation",
     *      @OA\Parameter(
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     * @param \App\Components\Teams\Models\Team $team
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function mineTeams(Team $team)
    {
        $this->authorize('jobs.view');
        $this->authorize('isMemberOf', $team);
        $jobList = $this->service->getByTeam($team->id);

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/previous",
     *      tags={"Jobs"},
     *      summary="Returns list previous jobs that were created from the same recurring job.",
     *      description="Returns list previous jobs.
    `jobs.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier for getting previous jobs",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RecurringJobListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listPrevious(Job $job)
    {
        $this->authorize('jobs.view');

        return JobListResponse::make($job->getPreviousJobs());
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine/no-contact",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of all jobs which are has no contact for 24 hours.",
     *      description="Returns list of all jobs which are has no contact for 24 hours. `jobs.view` permission is
    required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function noContact24Hours()
    {
        $this->authorize('jobs.view');

        $jobList = $this->service->getNoContact24Hours(Auth::id());

        return JobListResponse::make($jobList);
    }

    /**
     * @OA\Get(
     *      path="/jobs/mine/upcoming-kpi",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of all jobs which are has upcoming kpi.",
     *      description="Returns list of all jobs which are has upcoming kpi. `jobs.view` permission is
    required to perform this operation",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function upcomingKpi()
    {
        $this->authorize('jobs.view');

        $jobList = $this->service->getUpcomingKpi(Auth::id());

        return JobListResponse::make($jobList);
    }
}
