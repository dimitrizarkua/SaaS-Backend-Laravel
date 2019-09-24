<?php

namespace App\Http\Controllers\Users;

use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\Users\Interfaces\UserProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateAvatarRequest;
use App\Http\Requests\Users\UpdateUserProfileRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Users\MeResponse;
use App\Http\Responses\Users\UserLocationsResponse;
use App\Http\Responses\Users\UserProfileResponse;
use App\Http\Responses\Users\UserTeamsResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * Class UserProfileController
 *
 * @package App\Http\Controllers
 */
class UserProfileController extends Controller
{
    /** @var \App\Components\Users\Interfaces\UserProfileServiceInterface */
    private $userService;

    /**
     * @var RBACServiceInterface
     */
    private $RBACService;

    /**
     * UserProfileController constructor.
     *
     * @param \App\Components\Users\Interfaces\UserProfileServiceInterface $userService
     * @param \App\Components\RBAC\Interfaces\RBACServiceInterface         $RBACService
     */
    public function __construct(UserProfileServiceInterface $userService, RBACServiceInterface $RBACService)
    {
        $this->userService = $userService;
        $this->RBACService = $RBACService;
    }

    /**
     * @OA\Get(
     *     path="/me",
     *     summary="Returns profile of currently authenticated user",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *     operationId="getUserProfile",
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(ref="#/components/schemas/MeResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function getProfile(Request $request)
    {
        $user            = $request->user();
        $userPermissions = $this->RBACService
            ->getUsersService()
            ->getPermissions($user->id);

        return new MeResponse([
            'user'        => $user,
            'permissions' => $userPermissions,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/me",
     *     summary="Allows to update user's profile",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateUserProfileRequest")
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param \App\Http\Requests\Users\UpdateUserProfileRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Throwable
     */
    public function updateProfile(UpdateUserProfileRequest $request)
    {
        $user = $request->user();
        $user->fillFromRequest($request);

        return new UserProfileResponse($user);
    }

    /**
     * @OA\Get(
     *     path="/me/locations",
     *     summary="Returns locations which currently authenticated user is member of",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *     operationId="getUserLocations",
     *     @OA\Response(
     *         response=200,
     *         description="User locations",
     *         @OA\JsonContent(ref="#/components/schemas/UserLocationsResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function getLocations(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return new UserLocationsResponse($user->locations);
    }

    /**
     * @OA\Get(
     *     path="/me/teams",
     *     summary="Returns teams which currently authenticated user is member of",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *     operationId="getUserTeams",
     *     @OA\Response(
     *         response=200,
     *         description="User teams",
     *         @OA\JsonContent(ref="#/components/schemas/UserTeamsResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     )
     * )
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function getTeams(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return new UserTeamsResponse($user->teams);
    }

    /**
     * @OA\Post(
     *     path="/me/avatar",
     *     summary="Update avatar",
     *     description="Allows user to update avatar. Image dimensions are limited to 300x300 pixels",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/UpdateAvatarRequest")
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *        response=422,
     *        description="Validation error",
     *        @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param \App\Http\Requests\Users\UpdateAvatarRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $user = $this->userService->updateAvatar(Auth::id(), $request->photo());

        return new UserProfileResponse($user);
    }

    /**
     * @OA\Delete(
     *     path="/me/avatar",
     *     summary="Allows user to delete avatar",
     *     tags={"Users"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response="405",
     *         description="Not allowed. Avatar not set."
     *     )
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function deleteAvatar()
    {
        $this->userService->deleteAvatar(Auth::id());

        return ApiOKResponse::make();
    }
}
