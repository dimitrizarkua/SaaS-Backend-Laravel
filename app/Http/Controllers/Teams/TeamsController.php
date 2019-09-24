<?php

namespace App\Http\Controllers\Teams;

use App\Components\Pagination\Paginator;
use App\Components\Teams\Interfaces\TeamsServiceInterface;
use App\Components\Teams\Models\Team;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\CreateTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Responses\Teams\TeamListResponse;
use App\Http\Responses\Teams\TeamMembersResponse;
use App\Http\Responses\Teams\TeamResponse;
use App\Models\User;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Class TeamsController
 *
 * @package App\Http\Controllers\Meetings
 */
class TeamsController extends Controller
{
    /**
     * @var TeamsServiceInterface
     */
    private $teamsService;

    /**
     * TeamsController constructor.
     *
     * @param TeamsServiceInterface $teamsService
     */
    public function __construct(TeamsServiceInterface $teamsService)
    {
        $this->teamsService = $teamsService;
    }

    /**
     * @OA\Get(
     *      path="/teams",
     *      tags={"Teams"},
     *      summary="Returns list of all teams",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TeamListResponse")
     *      ),
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): ApiResponse
    {
        $this->authorize('teams.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Team::paginate(Paginator::resolvePerPage());

        return TeamListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/teams",
     *      tags={"Teams"},
     *      summary="Allows to create new team",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/CreateTeamRequest")
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TeamResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Teams\CreateTeamRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateTeamRequest $request): ApiResponse
    {
        $this->authorize('teams.create');

        $team = Team::create($request->validated());

        return TeamResponse::make($team, null, HttpResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/teams/{id}",
     *      tags={"Teams"},
     *      summary="Get specific team info",
     *      description="Returns info about specific team",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TeamResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Teams\Models\Team $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Team $team): ApiResponse
    {
        $this->authorize('teams.view');

        return TeamResponse::make($team);
    }

    /**
     * @OA\Patch(
     *      path="/teams/{id}",
     *      tags={"Teams"},
     *      summary="Allows to update existing team",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/UpdateTeamRequest")
     *
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NoteResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Teams\UpdateTeamRequest $request
     * @param \App\Components\Teams\Models\Team          $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateTeamRequest $request, Team $team): ApiResponse
    {
        $this->authorize('teams.update');

        $team->fillFromRequest($request);

        return TeamResponse::make($team);
    }

    /**
     * @OA\Delete(
     *      path="/teams/{id}",
     *      tags={"Teams"},
     *      summary="Remove existing team",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Teams\Models\Team $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Team $team): ApiResponse
    {
        $this->authorize('teams.delete');

        $team->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/teams/{id}/users",
     *     summary="Returns team members",
     *     tags={"Teams"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     operationId="getTeamMembers",
     *     @OA\Response(
     *         response=200,
     *         description="Team members",
     *         @OA\JsonContent(ref="#/components/schemas/TeamMembersResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param \App\Components\Teams\Models\Team $team
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMembers(Team $team)
    {
        $this->authorize('teams.view');

        return TeamMembersResponse::make($team->users);
    }

    /**
     * @OA\Post(
     *      path="/teams/{team_id}/users/{user_id}",
     *      tags={"Teams"},
     *      summary="Add user to specific team",
     *      description="Allows to add user to specific team",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Team or user doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. User has been added earlier to this team.",
     *      ),
     * )
     *
     * @param Team $team
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addUser(Team $team, User $user): ApiResponse
    {
        $this->authorize('teams.modify_members');

        $this->teamsService->addUser($team->id, $user->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/teams/{team_id}/users/{user_id}",
     *      tags={"Teams"},
     *      summary="Delete user from specific team",
     *      description="Allows delete user from specific team",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Team or user doesn't exist.",
     *      ),
     * )
     *
     * @param Team $team
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteUser(Team $team, User $user): ApiResponse
    {
        $this->authorize('teams.modify_members');

        $this->teamsService->removeUser($team->id, $user->id);
        return ApiOKResponse::make();
    }
}
