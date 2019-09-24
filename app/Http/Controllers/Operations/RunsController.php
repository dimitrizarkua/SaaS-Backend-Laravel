<?php

namespace App\Http\Controllers\Operations;

use App\Components\Operations\Interfaces\RunsServiceInterface;
use App\Components\Operations\Models\JobRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CreateRunRequest;
use App\Http\Requests\Operations\CreateRunsFromTemplateRequest;
use App\Http\Requests\Operations\ListRunsRequest;
use App\Http\Requests\Operations\ScheduleTaskRequest;
use App\Http\Requests\Operations\UpdateRunRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Operations\FullRunResponse;
use App\Http\Responses\Operations\RunListResponse;
use App\Http\Responses\Operations\RunResponse;

/**
 * Class RunsController
 *
 * @package App\Http\Controllers\Operations
 */
class RunsController extends Controller
{
    /** @var \App\Components\Operations\Interfaces\RunsServiceInterface $service */
    private $service;

    /**
     * RunsController constructor.
     *
     * @param \App\Components\Operations\Interfaces\RunsServiceInterface $service
     */
    public function __construct(RunsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/runs",
     *      tags={"Operations"},
     *      summary="Returns list of all location's runs",
     *      description="Allows to retrieve runs assigned to the specified location.
    `operations.runs.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="query",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          required=true,
     *          description="Requested date",
     *          @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2018-11-10"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested location could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\ListRunsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listLocationRuns(ListRunsRequest $request)
    {
        $this->authorize('operations.runs.view');

        $runs = $this->service->listLocationRuns($request->getLocationId(), $request->getDate());

        return RunListResponse::make($runs);
    }

    /**
     * @OA\Get(
     *      path="/operations/runs/{id}",
     *      tags={"Operations"},
     *      summary="Retrieve information about specific run",
     *      description="Allows to retrieve information about specific run.
    `operations.runs.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullRunResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Operations\Models\JobRun $run
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(JobRun $run)
    {
        $this->authorize('operations.runs.view');

        return FullRunResponse::make($run);
    }

    /**
     * @OA\Post(
     *      path="/operations/runs",
     *      tags={"Operations"},
     *      summary="Create new run",
     *      description="Allows to create new run for the specified location.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateRunRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateRunRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateRunRequest $request)
    {
        $this->authorize('operations.runs.manage');

        $run = $this->service->createRun(
            $request->getLocationId(),
            $request->getDate(),
            $request->getName()
        );

        return RunResponse::make($run, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/operations/runs/{id}",
     *      tags={"Operations"},
     *      summary="Update existing run",
     *      description="Allows to update existing run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateRunRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunResponse")
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
     * @param \App\Http\Requests\Operations\UpdateRunRequest $request
     * @param \App\Components\Operations\Models\JobRun       $run
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateRunRequest $request, JobRun $run)
    {
        $this->authorize('operations.runs.manage');

        $run->update([
            'name' => $request->getName(),
        ]);

        return RunResponse::make($run);
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/{id}",
     *      tags={"Operations"},
     *      summary="Delete existing run",
     *      description="Allows to delete existing run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
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
     * @param int $runId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $runId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->deleteRun($runId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/{run_id}/crew/{user_id}",
     *      tags={"Operations"},
     *      summary="Assign a user to a run",
     *      description="Allows to assign a user to a run's crew.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
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
     * @param int $runId  Run id.
     * @param int $userId User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignUser(int $runId, int $userId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->assignUser($runId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/{run_id}/crew/{user_id}",
     *      tags={"Operations"},
     *      summary="Unassign a user from a run",
     *      description="Allows to unassign a user from a run's crew.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
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
     * @param int $runId  Run id.
     * @param int $userId User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignUser(int $runId, int $userId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->unassignUser($runId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/{run_id}/vehicles/{vehicle_id}",
     *      tags={"Operations"},
     *      summary="Assign a vehicle to a run",
     *      description="Allows to assign a vehicle to a run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
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
     * @param int $runId     Run id.
     * @param int $vehicleId Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignVehicle(int $runId, int $vehicleId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->assignVehicle($runId, $vehicleId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/{run_id}/vehicles/{vehicle_id}",
     *      tags={"Operations"},
     *      summary="Unassign a vehicle from a run",
     *      description="Allows to unassign a vehicle from a run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
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
     * @param int $runId     Run id.
     * @param int $vehicleId Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignVehicle(int $runId, int $vehicleId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->unassignVehicle($runId, $vehicleId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/{run_id}/tasks/{task_id}",
     *      tags={"Operations"},
     *      summary="Schedule a task",
     *      description="Allows to schedule a task and add it to a run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ScheduleTaskRequest")
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
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Task identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or task could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Task cannot be added to the run.",
     *      ),
     * )
     * @param \App\Http\Requests\Operations\ScheduleTaskRequest $request
     * @param int                                               $runId  Run id.
     * @param int                                               $taskId Task id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function scheduleTask(ScheduleTaskRequest $request, int $runId, int $taskId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->scheduleTask(
            $runId,
            $taskId,
            $request->getStartsAt(),
            $request->getEndsAt()
        );

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/{run_id}/tasks/{task_id}",
     *      tags={"Operations"},
     *      summary="Remove a task from a run",
     *      description="Allows to remove a task from a run.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="run_id",
     *          in="path",
     *          required=true,
     *          description="Run identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Task identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested run or task could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Task is not assigned to this run.",
     *      ),
     * )
     * @param int $runId  Run id.
     * @param int $taskId Task id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeTask(int $runId, int $taskId)
    {
        $this->authorize('operations.runs.manage');

        $this->service->removeTask($runId, $taskId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/from-template/{template_id}",
     *      tags={"Operations"},
     *      summary="Create new runs from a template",
     *      description="Allows to create new runs from a template.
    `operations.runs.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="template_id",
     *          in="path",
     *          required=true,
     *          description="Template identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateRunsFromTemplateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateRunsFromTemplateRequest $request
     * @param int                                                         $templateId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createFromTemplate(CreateRunsFromTemplateRequest $request, int $templateId)
    {
        $this->authorize('operations.runs.manage');

        $runs = $this->service->createRunsFromTemplate($templateId, $request->getDate());

        return RunListResponse::make($runs);
    }
}
