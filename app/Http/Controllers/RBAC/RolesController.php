<?php

namespace App\Http\Controllers\RBAC;

use App\Components\Pagination\Paginator;
use App\Components\RBAC\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\CreateRoleRequest;
use App\Http\Requests\RBAC\UpdateRoleRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\RBAC\FullRoleResponse;
use App\Http\Responses\RBAC\RoleListResponse;
use App\Http\Responses\RBAC\RoleResponse;
use OpenApi\Annotations as OA;

/**
 * Class RolesController
 *
 * @package App\Http\Controllers\RBAC
 */
class RolesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/roles",
     *      tags={"Roles"},
     *      summary="Returns list of all roles",
     *      description="Returns list of all roles",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RoleListResponse")
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('roles.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Role::paginate(Paginator::resolvePerPage());

        return RoleListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/roles",
     *      tags={"Roles"},
     *      summary="Allows to create new role",
     *      description="Allows to create new role",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateRoleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateRoleRequest $request)
    {
        $this->authorize('roles.create');
        $role = Role::create($request->validated());
        if (null === $role->display_name) {
            $role->display_name = $role->name;
        }
        $role->saveOrFail();

        return RoleResponse::make($role, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/roles/{id}",
     *      tags={"Roles"},
     *      summary="Returns full information about specific role",
     *      description="Returns full information about specific user",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Role $role)
    {
        $this->authorize('roles.view');

        return FullRoleResponse::make($role);
    }

    /**
     * @OA\Patch(
     *      path="/roles/{id}",
     *      tags={"Roles"},
     *      summary="Update existing user",
     *      description="Update existing user",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateRoleRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullRoleResponse")
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
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('roles.update');
        $role->fillFromRequest($request);

        return FullRoleResponse::make($role);
    }

    /**
     * @OA\Delete(
     *      path="/roles/{id}",
     *      tags={"Roles"},
     *      summary="Delete existing role",
     *      description="Delete existing role",
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
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Role $role)
    {
        $this->authorize('roles.delete');
        $role->delete();

        return ApiOKResponse::make();
    }
}
