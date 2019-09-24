<?php

namespace App\Http\Controllers\Operations;

use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Models\JobTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\ListTasksRequest;
use App\Http\Requests\Operations\SearchTasksRequest;
use App\Http\Responses\Operations\SearchTasksResponse;
use App\Http\Responses\Operations\TaskListResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class TasksController
 *
 * @package App\Http\Controllers\Operations
 */
class TasksController extends Controller
{
    /** @var \App\Components\Jobs\Interfaces\JobTasksServiceInterface */
    private $service;

    /**
     * TasksController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobTasksServiceInterface $service
     */
    public function __construct(JobTasksServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/tasks",
     *      tags={"Operations"},
     *      summary="Returns list of all location's tasks which haven't been scheduled",
     *      description="Allows to retrieve all unscheduled tasks assigned to the specified location.
                        `operations.tasks.view` permission is required to perform this operation",
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
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/TaskListResponse")
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
     * @param \App\Http\Requests\Operations\ListTasksRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listLocationTasks(ListTasksRequest $request)
    {
        $this->authorize('operations.tasks.view');

        $tasks = $this->service->listUnscheduledLocationTasks($request->getLocationId());

        return TaskListResponse::make($tasks);
    }

    /**
     * @OA\Get(
     *      path="/operations/tasks/search",
     *      tags={"Operations", "Search"},
     *      summary="Search for tasks",
     *      description="Search for tasks",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to search tasks by location id.
                            `operations.tasks.view` permission is required to perform this operation",
     *         @OA\Schema(
     *            type="integer",
     *            example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Allows to filter tasks by name or job id",
     *         @OA\Schema(
     *            type="string",
     *            example="Task 112233"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SearchTasksResponse")
     *      ),
     *      @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\SearchTasksRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchTasksRequest $request)
    {
        $this->authorize('operations.tasks.view');

        $query = JobTask::search($request->validated())->take(10);
        $tasks = Collection::make(mapElasticResults($query->raw()));

        return SearchTasksResponse::make($tasks);
    }

    /**
     * @OA\Get(
     *      path="/operations/tasks/mine",
     *      tags={"Operations"},
     *      summary="Get tasks assigned to the currently logged user",
     *      description="Allows to get tasks assigned to the currently logged user.
                        `operations.tasks.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TaskListResponse")
     *      )
     * )
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getMineTasks()
    {
        $this->authorize('operations.tasks.view');

        $tasks = $this->service->getUserTasks(Auth::id());

        return TaskListResponse::make($tasks);
    }
}
