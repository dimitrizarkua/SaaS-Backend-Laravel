<?php

namespace App\Http\Controllers\Search;

use App\Components\Search\Models\UserAndTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\UserAndTeamsSearchRequest;
use App\Http\Responses\Search\UsersAndTeamsSearchResponse;
use OpenApi\Annotations as OA;

/**
 * Class UsersAndTeamsController
 *
 * @package App\Http\Controllers\Search
 */
class UsersAndTeamsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/search/users-and-teams",
     *      tags={"Search", "Teams", "Users"},
     *      summary="Allows to search users and teams by name",
     *      description="Allows to search users and teams by name",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Search terms",
     *         @OA\Schema(
     *            type="string",
     *            example="John",
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UsersAndTeamsSearchResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Search\UserAndTeamsSearchRequest $request
     *
     * @return UsersAndTeamsSearchResponse
     */
    public function search(UserAndTeamsSearchRequest $request)
    {
        $locationIds = auth()->user()
            ->locations
            ->pluck('id')
            ->toArray();

        $results = UserAndTeam::filter($request->getTerm(), $locationIds);

        return UsersAndTeamsSearchResponse::make($results);
    }
}
