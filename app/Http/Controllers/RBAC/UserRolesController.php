<?php

namespace App\Http\Controllers\RBAC;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\AttachRoleRequest;
use App\Http\Responses\RBAC\FullRoleListResponse;
use OpenApi\Annotations as OA;

/**
 * Class UserRolesController
 *
 * @package App\Http\Controllers\Api
 */
class UserRolesController extends Controller
{
    /**
     * @var \App\Components\RBAC\Interfaces\RBACServiceInterface
     */
    private $service;

    /**
     * PermissionsController constructor.
     *
     * @param RBACServiceInterface $service
     */
    public function __construct(RBACServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/users/{user_id}/roles",
     *      tags={"Roles","Users"},
     *      summary="Returns list of all roles for specific user.",
     *      description="Returns list of all roles for specific user.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Requested user identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleListResponse"),
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param integer $userId
     *
     * @return FullRoleListResponse
     */
    public function getUserRoles(int $userId): FullRoleListResponse
    {
        $roles = $this->service->getUsersService()
            ->getRoles($userId);

        return FullRoleListResponse::make($roles);
    }

    /**
     * @OA\Post(
     *      path="/users/{user_id}/roles",
     *      tags={"Roles"},
     *      summary="Allows to attach list of roles to specific user",
     *      description="Allows to attach list of roles to specific user. **users.update** permission is required
    to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Requested user identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachRoleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleListResponse"),
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=405,
     *          description="Not allowed. Role {role_name} already attached to this user",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param integer           $userId
     * @param AttachRoleRequest $request
     *
     * @return FullRoleListResponse
     * @throws \Throwable
     */
    public function attachRole(int $userId, AttachRoleRequest $request): FullRoleListResponse
    {
        $this->authorize('users.update');

        $userService = $this->service->getUsersService();
        $roles = $userService
            ->getRoles($userId);

        if ($roles->isNotEmpty()) {
            throw new NotAllowedException(
                'The user already has a role. Please detach all roles before attaching new.'
            );
        }

        if (count($request->getRoleIds()) > 1) {
            throw new NotAllowedException(
                'Multiple user roles attaching is forbidden.'
            );
        }

        try {
            $userService->attachRoles($userId, $request->getRoleIds());
        } catch (InvalidArgumentException $e) {
            throw new NotAllowedException($e->getMessage());
        }

        $roles = $userService->getRoles($userId);

        return FullRoleListResponse::make($roles);
    }

    /**
     * @OA\Delete(
     *      path="/users/{user_id}/roles",
     *      tags={"Roles"},
     *      summary="Allows to detach list of roles from specific user",
     *      description="Allows to detach list of roles from specific user. **users.update** permission is required
    to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Requested user identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachRoleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleListResponse"),
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Role {role_name} is not attached to this user",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param integer           $userId
     * @param AttachRoleRequest $request
     *
     * @return FullRoleListResponse
     * @throws \Throwable
     */
    public function detachRoles(int $userId, AttachRoleRequest $request): FullRoleListResponse
    {
        $this->authorize('users.update');

        $this->service->getUsersService()->detachRoles($userId, $request->getRoleIds());

        $roles = $this->service->getUsersService()
            ->getRoles($userId);

        return FullRoleListResponse::make($roles);
    }

    /**
     * @OA\Patch(
     *      path="/users/{user_id}/roles",
     *      tags={"Roles"},
     *      summary="Allows to change of roles to specific user",
     *      description="Allows to attach list of roles to specific user. **users.update** permission is required
    to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Requested user identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachRoleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleListResponse"),
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=405,
     *          description="Not allowed. Role {role_name} already attached to this user",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param AttachRoleRequest $request
     * @param integer           $userId
     *
     * @return FullRoleListResponse
     * @throws \Throwable
     */
    public function changeRole(int $userId, AttachRoleRequest $request): FullRoleListResponse
    {
        $this->authorize('users.update');

        $userService = $this->service->getUsersService();
        try {
            $userService->changeRole($userId, $request->getRoleIds());
        } catch (InvalidArgumentException $e) {
            throw new NotAllowedException($e->getMessage());
        }

        $roles = $userService->getRoles($userId);

        return FullRoleListResponse::make($roles);
    }
}
