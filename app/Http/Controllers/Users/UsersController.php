<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SearchUsersForMentionsRequest;
use App\Http\Responses\Users\UserSearchResponse;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 *
 * @package App\Http\Controllers
 */
class UsersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users/search/mentions",
     *     summary="Allows to search for users when mentioning them in editors",
     *     description="Allows to get concise information about users.
    The endpoints is supposed to be used by frontend applications when a user start mentioning someone.
    You should search by the name. The result set will have at most 10 items.",
     *     tags={"Users", "Search"},
     *     security={{"passport": {}}},
     *     operationId="searchUsersForMentions",
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=false,
     *          description="First or last name of the user",
     *          @OA\Schema(
     *              type="string",
     *              example="John Doe",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Matching users",
     *         @OA\JsonContent(ref="#/components/schemas/UserSearchResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param SearchUsersForMentionsRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function search(SearchUsersForMentionsRequest $request)
    {
        $authorizedUserLocationIds = auth()->user()->locations->pluck('id')->toArray();
        $response                  = User::searchForMentions($request->validated(), $authorizedUserLocationIds);

        return new UserSearchResponse($response);
    }
}
