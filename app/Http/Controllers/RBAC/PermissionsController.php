<?php

namespace App\Http\Controllers\RBAC;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\AttachPermissionRequest;
use App\Http\Responses\Error\NotFoundResponse;
use App\Http\Responses\RBAC\PermissionListResponse;
use App\Http\Responses\RBAC\PermissionResponse;
use OpenApi\Annotations as OA;

/**
 * Class PermissionsController
 *
 * @package App\Http\Controllers\Api
 */
class PermissionsController extends Controller
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
     *      path="/permissions",
     *      tags={"Roles"},
     *      summary="Returns list of all permissions",
     *      description="Returns list of all roles",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionListResponse")
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('permissions.view');
        $permissionsCollection = $this->service->getPermissionDataProvider()
            ->getAllPermissions();

        return PermissionListResponse::make($permissionsCollection);
    }

    /**
     * @OA\Get(
     *      path="/permissions/{permission_name}",
     *      tags={"Roles"},
     *      summary="Returns full information about specific permission",
     *      description="Returns full information about specific permission",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="permission_name",
     *          in="path",
     *          required=true,
     *          description="Requested object identifier",
     *          @OA\Schema(
     *              type="string",
     *              example="users.update",
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($permissionName)
    {
        $this->authorize('permissions.view');
        try {
            $permission = $this->service->getPermissionDataProvider()
                ->getPermissionInstance($permissionName);
        } catch (InvalidArgumentException $e) {
            return new NotFoundResponse(sprintf('Permission \'%s\' doesn\'t exists', $permissionName));
        }

        return PermissionResponse::make($permission);
    }

    /**
     * @OA\Get(
     *      path="/roles/{role_id}/{permission_name}",
     *      tags={"Roles"},
     *      summary="Returns full information about specific permission",
     *      description="Returns full information about specific permission",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="role_id",
     *          in="path",
     *          required=true,
     *          description="Role identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     */
    public function getRolePermissions($roleId)
    {
        $permissions = $this->service->getRolesService()
            ->getPermissions($roleId);

        return PermissionListResponse::make($permissions);
    }

    /**
     * @OA\Post(
     *      path="/roles/{role_id}/permissions",
     *      tags={"Roles"},
     *      summary="Allows to attach list of permissions to specific role",
     *      description="Allows to attach list of permissions to specific role",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="role_id",
     *          in="path",
     *          required=true,
     *          description="Role identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachPermissionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionListResponse")
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=405,
     *          description="Not allowed.",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @throws \Throwable
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function attachPermissionsToRole(int $roleId, AttachPermissionRequest $request)
    {
        try {
            $this->service->getRolesService()
                ->attachPermissions($roleId, $request->getPermissionNames());
        } catch (InvalidArgumentException $e) {
            throw new NotAllowedException($e->getMessage());
        }

        $permissions = $this->service->getRolesService()
            ->getPermissions($roleId);

        return PermissionListResponse::make($permissions);
    }


    /**
     * @OA\Delete(
     *      path="/roles/{role_id}/permissions",
     *      tags={"Roles"},
     *      summary="Allows to detach list of permissions to specific role",
     *      description="Allows to detach list of permissions to specific role",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="role_id",
     *          in="path",
     *          required=true,
     *          description="Role identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachPermissionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PermissionListResponse")
     *       ),
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
     *
     * @throws \Throwable
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function detachPermissionsFromRole($roleId, AttachPermissionRequest $request)
    {
        $this->service->getRolesService()
            ->detachPermissions($roleId, $request->getPermissionNames());


        $permissions = $this->service->getRolesService()
            ->getPermissions($roleId);

        return PermissionListResponse::make($permissions);
    }
}
