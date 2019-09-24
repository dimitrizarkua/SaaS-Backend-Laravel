<?php

namespace App\Http\Controllers\Operations;

use App\Components\Operations\Interfaces\RunTemplateRunsServiceInterface;
use App\Components\Operations\Models\JobRunTemplateRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CreateRunTemplateRunRequest;
use App\Http\Requests\Operations\UpdateRunTemplateRunRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Operations\FullRunTemplateRunResponse;
use App\Http\Responses\Operations\RunTemplateRunListResponse;
use App\Http\Responses\Operations\RunTemplateRunResponse;

/**
 * Class RunTemplateRunsController
 *
 * @package App\Http\Controllers\Operations
 */
class RunTemplateRunsController extends Controller
{
    /** @var \App\Components\Operations\Interfaces\RunTemplateRunsServiceInterface $service */
    private $service;

    /**
     * VehiclesController constructor.
     *
     * @param \App\Components\Operations\Interfaces\RunTemplateRunsServiceInterface $service
     */
    public function __construct(RunTemplateRunsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/runs/templates/{template_id}/runs",
     *      tags={"Operations"},
     *      summary="Returns list of all template's runs",
     *      description="Allows to retrieve runs assigned to the specified template.
                        `operations.runs_templates.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateRunListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested location could not be found.",
     *      ),
     * )
     * @param int $templateId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listTemplateRuns(int $templateId)
    {
        $this->authorize('operations.runs_templates.view');

        $templates = $this->service->listTemplateRuns($templateId);

        return RunTemplateRunListResponse::make($templates);
    }

    /**
     * @OA\Get(
     *      path="/operations/runs/templates/{template_id}/runs/{id}",
     *      tags={"Operations"},
     *      summary="Retrieve information about specific template run",
     *      description="Allows to retrieve information about specific template run.
                        `operations.runs_templates.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullRunTemplateRunResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param int                                                 $templateId
     * @param \App\Components\Operations\Models\JobRunTemplateRun $run
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function viewTemplateRun(int $templateId, JobRunTemplateRun $run)
    {
        $this->authorize('operations.runs_templates.view');

        return FullRunTemplateRunResponse::make($run);
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/templates/{template_id}/runs",
     *      tags={"Operations"},
     *      summary="Create new template run",
     *      description="Allows to create new template run.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateRunTemplateRunRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateRunResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateRunTemplateRunRequest $request
     * @param int                                                       $templateId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addTemplateRun(CreateRunTemplateRunRequest $request, int $templateId)
    {
        $this->authorize('operations.runs_templates.manage');

        $run = $this->service->createTemplateRun($templateId, $request->getName());

        return RunTemplateRunResponse::make($run, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/operations/runs/templates/{template_id}/runs/{id}",
     *      tags={"Operations"},
     *      summary="Update existing template run",
     *      description="Allows to update existing template run.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateRunTemplateRunRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateRunResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\UpdateRunTemplateRunRequest $request
     * @param int                                                       $templateId
     * @param \App\Components\Operations\Models\JobRunTemplateRun       $run
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function updateTemplateRun(UpdateRunTemplateRunRequest $request, int $templateId, JobRunTemplateRun $run)
    {
        $this->authorize('operations.runs_templates.manage');

        $run->fillFromRequest($request);

        return RunTemplateRunResponse::make($run);
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/templates/{template_id}/runs/{id}",
     *      tags={"Operations"},
     *      summary="Delete existing template run",
     *      description="Allows to delete existing template run.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not be deleted since another entity refers to it.",
     *      ),
     * )
     * @param int $templateId
     * @param int $runId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteTemplateRun(int $templateId, int $runId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->deleteTemplateRun($runId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/templates/{template_id}/runs/{run_id}/crew/{user_id}",
     *      tags={"Operations"},
     *      summary="Assign a user to a template run",
     *      description="Allows to assign a user to a template run's crew.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="run_id",
     *          in="path",
     *          required=true,
     *          description="Run identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Assigned user identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or user could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. User is already assigned to this run.",
     *      ),
     * )
     * @param int $templateId Template id.
     * @param int $runId      Run id.
     * @param int $userId     User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignUser(int $templateId, int $runId, int $userId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->assignUser($runId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/operations/templates/{template_id}/runs/{run_id}/crew/{user_id}",
     *      tags={"Operations"},
     *      summary="Unassign a user from a template run",
     *      description="Allows to unassign a user from a template run's crew.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="run_id",
     *          in="path",
     *          required=true,
     *          description="Run identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="Unassigned user identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or user could not be found.",
     *      ),
     * )
     * @param int $templateId Template id.
     * @param int $runId      Run id.
     * @param int $userId     User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignUser(int $templateId, int $runId, int $userId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->unassignUser($runId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/templates/{template_id}/runs/{run_id}/vehicles/{vehicle_id}",
     *      tags={"Operations"},
     *      summary="Assign a vehicle to a template run",
     *      description="Allows to assign a vehicle to a template run.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="run_id",
     *          in="path",
     *          required=true,
     *          description="Run identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="vehicle_id",
     *          in="path",
     *          required=true,
     *          description="Assigned vehicle identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or vehicle could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Vehicle is already assigned to this run.",
     *      ),
     * )
     * @param int $templateId Template id.
     * @param int $runId      Run id.
     * @param int $vehicleId  Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignVehicle(int $templateId, int $runId, int $vehicleId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->assignVehicle($runId, $vehicleId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/operations/templates/{template_id}/runs/{run_id}/vehicles/{vehicle_id}",
     *      tags={"Operations"},
     *      summary="Unassign a vehicle from a template run",
     *      description="Allows to unassign a vehicle from a template run.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="run_id",
     *          in="path",
     *          required=true,
     *          description="Run identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="vehicle_id",
     *          in="path",
     *          required=true,
     *          description="Unassigned vehicle identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or vehicle could not be found.",
     *      ),
     * )
     * @param int $templateId Template id.
     * @param int $runId      Run id.
     * @param int $vehicleId  Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignVehicle(int $templateId, int $runId, int $vehicleId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->unassignVehicle($runId, $vehicleId);

        return ApiOKResponse::make();
    }
}
