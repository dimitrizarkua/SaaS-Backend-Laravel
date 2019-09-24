<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\VO\JobTaskData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\ChangeJobTaskStatusRequest;
use App\Http\Requests\Jobs\CreateJobTaskRequest;
use App\Http\Requests\Jobs\SnoozeJobTaskRequest;
use App\Http\Requests\Jobs\UpdateJobTaskRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\FullJobTaskResponse;
use App\Http\Responses\Jobs\JobTaskListResponse;
use App\Http\Responses\Jobs\JobTaskResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * Class JobTasksController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobTasksController extends Controller
{
    /** @var \App\Components\Jobs\Interfaces\JobTasksServiceInterface */
    private $service;

    /**
     * JobTasksController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobTasksServiceInterface $service
     */
    public function __construct(JobTasksServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/tasks",
     *      tags={"Jobs"},
     *      summary="List all the job tasks",
     *      description="Allows to list all tasks added to the specific job.
                        `job.tasks.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested job could not be found.",
     *      ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listJobTasks(int $jobId)
    {
        $this->authorize('jobs.tasks.view');

        $tasks = $this->service->listJobTasks($jobId);

        return JobTaskListResponse::make($tasks);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tasks",
     *      tags={"Jobs"},
     *      summary="Create new job task",
     *      description="Allows to create new job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobTaskRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskResponse")
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobTaskRequest $request
     * @param int                                          $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function addJobTask(CreateJobTaskRequest $request, int $jobId)
    {
        $this->authorize('jobs.tasks.manage');

        $data    = new JobTaskData($request->validated());
        $jobTask = $this->service->createTask($data, $jobId, Auth::id());

        return JobTaskResponse::make($jobTask, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/tasks/{id}",
     *      tags={"Jobs"},
     *      summary="Retrieve information about specific job task",
     *      description="Allows to retrieve information about specific job task.
                        `job.tasks.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullJobTaskResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param int $jobId
     * @param int $taskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function viewJobTask(int $jobId, int $taskId)
    {
        $this->authorize('jobs.tasks.view');

        $task = $this->service->getTask($taskId);

        return FullJobTaskResponse::make($task);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/tasks/{id}",
     *      tags={"Jobs"},
     *      summary="Update existing job task",
     *      description="Allows to update existing job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobTaskRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/JobTaskResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\UpdateJobTaskRequest $request
     * @param int                                          $jobId
     * @param \App\Components\Jobs\Models\JobTask          $task
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function updateJobTask(UpdateJobTaskRequest $request, int $jobId, JobTask $task)
    {
        $this->authorize('jobs.tasks.manage');

        if ($task->job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }
        $task->fillFromRequest($request);

        return JobTaskResponse::make($task);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tasks/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing job task",
     *      description="Allows to delete existing job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param int $jobId
     * @param int $jobTaskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteJobTask(int $jobId, int $jobTaskId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->deleteTask($jobTaskId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/tasks/{id}/status",
     *      tags={"Jobs"},
     *      summary="Change status of a job task",
     *      description="Allows to change status of an existing job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ChangeJobTaskStatusRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\ChangeJobTaskStatusRequest $request
     * @param int                                                $jobId
     * @param int                                                $jobTaskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeStatus(ChangeJobTaskStatusRequest $request, int $jobId, int $jobTaskId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->changeStatus($jobTaskId, $request->getStatus());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/tasks/{id}/status/scheduled",
     *      tags={"Jobs"},
     *      summary="Change scheduled portion of the job task's status",
     *      description="Allows to change scheduled portion of the task's status.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ChangeJobTaskStatusRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Tasks of this type cannot be scheduled or job is closed.",
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\ChangeJobTaskStatusRequest $request
     * @param int                                                $jobId
     * @param int                                                $jobTaskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeScheduledStatus(ChangeJobTaskStatusRequest $request, int $jobId, int $jobTaskId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->changeScheduledStatus($jobTaskId, $request->getStatus());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tasks/{id}/snooze",
     *      tags={"Jobs"},
     *      summary="Snooze specific job task",
     *      description="Allows to snooze specific job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SnoozeJobTaskRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\SnoozeJobTaskRequest $request
     * @param int                                          $jobId
     * @param int                                          $taskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function snoozeTask(SnoozeJobTaskRequest $request, int $jobId, int $taskId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->snoozeTask($taskId, $request->get('snoozed_until'));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tasks/{id}/unsnooze",
     *      tags={"Jobs"},
     *      summary="Un-snooze specific job task",
     *      description="Allows to un-snooze specific job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param int $jobId
     * @param int $taskId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unsnoozeTask(int $jobId, int $taskId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->unsnoozeTask($taskId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tasks/{task_id}/crew/{user_id}",
     *      tags={"Jobs"},
     *      summary="Assign a user to a job task",
     *      description="Allows to assign a user to a job task's crew.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
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
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. User is already assigned to this task or job is closed.",
     *      ),
     * )
     * @param int $jobId  Job id.
     * @param int $taskId Task id.
     * @param int $userId User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignUser(int $jobId, int $taskId, int $userId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->assignUser($taskId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tasks/{task_id}/crew/{user_id}",
     *      tags={"Jobs"},
     *      summary="Unassign a user from a job task",
     *      description="Allows to unassign a user from a job task's crew.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
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
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param int $jobId  Job id.
     * @param int $taskId Task id.
     * @param int $userId User id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignUser(int $jobId, int $taskId, int $userId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->unassignUser($taskId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tasks/{task_id}/vehicles/{vehicle_id}",
     *      tags={"Jobs"},
     *      summary="Assign a vehicle to a job task",
     *      description="Allows to assign a vehicle to a job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
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
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Vehicle is already assigned to this task or job is closed.",
     *      ),
     * )
     * @param int $jobId     Job id.
     * @param int $taskId    Task id.
     * @param int $vehicleId Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignVehicle(int $jobId, int $taskId, int $vehicleId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->assignVehicle($taskId, $vehicleId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tasks/{task_id}/vehicles/{vehicle_id}",
     *      tags={"Jobs"},
     *      summary="Unassign a vehicle from a job task",
     *      description="Allows to unassign a vehicle from a job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
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
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param int $jobId     Job id.
     * @param int $taskId    Task id.
     * @param int $vehicleId Vehicle id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignVehicle(int $jobId, int $taskId, int $vehicleId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->unassignVehicle($taskId, $vehicleId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tasks/{task_id}/teams/{team_id}",
     *      tags={"Jobs"},
     *      summary="Assign a team to a job task",
     *      description="Allows to assign a team to a job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Team identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Team is already assigned to this task or job is closed.",
     *      ),
     * )
     * @param int $jobId  Job id.
     * @param int $taskId Task id.
     * @param int $teamId Team id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function assignTeam(int $jobId, int $taskId, int $teamId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->assignTeam($taskId, $teamId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tasks/{task_id}/teams/{team_id}",
     *      tags={"Jobs"},
     *      summary="Unassign a team from a job task",
     *      description="Allows to unassign a team from a job task.
                        `job.tasks.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="task_id",
     *          in="path",
     *          required=true,
     *          description="Job task identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="team_id",
     *          in="path",
     *          required=true,
     *          description="Unassigned team identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param int $jobId  Job id.
     * @param int $taskId Task id.
     * @param int $teamId Team id.
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unassignTeam(int $jobId, int $taskId, int $teamId)
    {
        $this->authorize('jobs.tasks.manage');

        $this->service->unassignTeam($taskId, $teamId);

        return ApiOKResponse::make();
    }
}
