<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobAreasServiceInterface;
use App\Components\Jobs\Models\VO\JobRoomData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobRoomRequest;
use App\Http\Requests\Jobs\UpdateJobRoomRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobRoomListResponse;
use App\Http\Responses\Jobs\JobRoomResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobAreasController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobAreasController extends Controller
{
    /**
     * @var JobAreasServiceInterface
     */
    protected $service;

    /**
     * JobAreasController constructor.
     *
     * @param JobAreasServiceInterface $service
     */
    public function __construct(JobAreasServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/areas",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Get list of all rooms attached to a job.",
     *     description="Return list of all rooms attached to a job. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobRoomListResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     * )
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(int $jobId)
    {
        $this->authorize('jobs.view');

        $rooms = $this->service->getRoomList($jobId);

        return JobRoomListResponse::make($rooms);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/areas/{area_id}",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Get specified job room.",
     *     description="Return specified job room. **`jobs.view`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="area_id",
     *         in="path",
     *         required=true,
     *         description="JobRoom identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobRoomResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     * )
     * @param int $jobId
     * @param int $roomId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $jobId, int $roomId)
    {
        $this->authorize('jobs.view');

        $rooms = $this->service->getRoom($jobId, $roomId);

        return JobRoomResponse::make($rooms);
    }

    /**
     * @OA\Post(
     *     path="/jobs/{job_id}/areas",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Add a room to a job.",
     *     description="Create a room and add it to a job. **`jobs.areas.manage`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateJobRoomRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobRoomResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not found. Job doesn't exist.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. No changes can be made to closed or cancelled job.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateJobRoomRequest $request
     * @param int                  $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \JsonMapper_Exception
     */
    public function store(CreateJobRoomRequest $request, int $jobId)
    {
        $this->authorize('jobs.areas.manage');
        $data    = new JobRoomData($request->validated());
        $jobRoom = $this->service->addRoom($data, $jobId);

        return JobRoomResponse::make($jobRoom, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/jobs/{job_id}/areas/{area_id}",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Update a job room.",
     *     description="Update a job room. **`jobs.areas.manage`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="area_id",
     *         in="path",
     *         required=true,
     *         description="JobRoom identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateJobRoomRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobRoomResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not found. Job doesn't exist.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. No changes can be made to closed or cancelled job.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param UpdateJobRoomRequest $request
     * @param int                  $jobId
     * @param int                  $roomId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function update(UpdateJobRoomRequest $request, int $jobId, int $roomId)
    {
        $this->authorize('jobs.areas.manage');
        $data    = new JobRoomData($request->validated());
        $jobRoom = $this->service->updateRoom($data, $jobId, $roomId);

        return JobRoomResponse::make($jobRoom);
    }

    /**
     * @OA\Delete(
     *     path="/jobs/{job_id}/areas/{area_id}",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Delete a job room.",
     *     description="Delete a job room. **`jobs.areas.manage`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="area_id",
     *         in="path",
     *         required=true,
     *         description="JobRoom identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. No changes can be made to closed or cancelled job.",
     *     ),
     * )
     * @param int $jobId
     * @param int $roomId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $jobId, int $roomId)
    {
        $this->authorize('jobs.areas.manage');

        $this->service->deleteRoom($jobId, $roomId);

        return ApiOKResponse::make();
    }
}
