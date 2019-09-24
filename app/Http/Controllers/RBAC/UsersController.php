<?php

namespace App\Http\Controllers\RBAC;

use App\Components\Pagination\Paginator;
use App\Components\Users\Interfaces\UserProfileServiceInterface;
use App\Events\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\CreateUserRequest;
use App\Http\Requests\RBAC\UpdateUserRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Users\UserListResponse;
use App\Http\Responses\Users\UserProfileResponse;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 *
 * @package App\Http\Controllers\RBAC
 */
class UsersController extends Controller
{
    /**
     * @OA\Get(
     *      path="/users",
     *      tags={"Users"},
     *      summary="Get list of users",
     *      description="Returns list of users",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('users.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = User::query()
            ->with('avatar')
            ->paginate(Paginator::resolvePerPage());

        return UserListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/users",
     *      tags={"Users"},
     *      summary="Create new user",
     *      description="Create new user",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateUserRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param CreateUserRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateUserRequest $request)
    {
        $this->authorize('users.create');

        $user = User::create($request->all());
        $user->setPassword($request->input('password'))
            ->saveOrFail();

        event(new UserCreated($user));

        return UserProfileResponse::make($user, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/users/{id}",
     *      tags={"Users"},
     *      summary="Returns full information about specific user",
     *      description="Returns full information about specific user",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $user)
    {
        $this->authorize('users.view');

        return UserProfileResponse::make($user);
    }

    /**
     * @OA\Patch(
     *      path="/users/{id}",
     *      tags={"Users"},
     *      summary="Allows to update specific user",
     *      description="Allows to update specific user",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateUserRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param UpdateUserRequest $request
     * @param User              $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('users.update');

        $user = app()->make(UserProfileServiceInterface::class)
            ->updateUser($user->id, $request->getUpdateUserData());

        return UserProfileResponse::make($user);
    }

    /**
     * @OA\Delete(
     *      path="/users/{id}",
     *      tags={"Users"},
     *      summary="Delete existing user",
     *      description="Delete existing user",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param User $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        $user->delete();

        return ApiOKResponse::make();
    }
}
