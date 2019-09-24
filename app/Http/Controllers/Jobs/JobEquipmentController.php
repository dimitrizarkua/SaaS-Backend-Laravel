<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobEquipmentServiceInterface;
use App\Components\Jobs\Models\VO\CreateJobEquipmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobEquipmentRequest;
use App\Http\Requests\Jobs\OverrideJobEquipmentIntervalsCountRequest;
use App\Http\Requests\Jobs\FinishJobEquipmentUsingRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\FullJobEquipmentResponse;
use App\Http\Responses\Jobs\JobEquipmentListResponse;
use App\Http\Responses\Jobs\JobEquipmentTotalAmountResponse;
use Illuminate\Support\Carbon;

/**
 * Class JobEquipmentController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobEquipmentController extends Controller
{
    /** @var \App\Components\Jobs\Interfaces\JobEquipmentServiceInterface */
    private $service;

    /**
     * JobEquipmentController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobEquipmentServiceInterface $service
     */
    public function __construct(JobEquipmentServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/equipment",
     *     tags={"Equipment", "Jobs"},
     *     summary="Get list of equipment used in this job",
     *     description="Returns list of equipment which are used in this job. **`jobs.usage.view`** permission is
    required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobEquipmentListResponse"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param int $jobId
     *
     * @return \App\Http\Responses\Jobs\JobEquipmentListResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(int $jobId)
    {
        $this->authorize('jobs.usage.view');
        $equipmentList = $this->service->getJobEquipmentList($jobId);

        return JobEquipmentListResponse::make($equipmentList);
    }

    /**
     * @OA\Post(
     *     path="/jobs/{job_id}/equipment",
     *     tags={"Equipment", "Jobs"},
     *     summary="Create new job equipment",
     *     description="Create new job equipment for specified job. **`jobs.usage.equipment.create`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateJobEquipmentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobEquipmentListResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. The job is closed.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param CreateJobEquipmentRequest $request
     * @param int                       $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Jobs\FullJobEquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \JsonMapper_Exception
     */
    public function store(CreateJobEquipmentRequest $request, int $jobId)
    {
        $this->authorize('jobs.usage.equipment.create');

        $createData   = new CreateJobEquipmentData($request->validated());
        $jobEquipment = $this->service->createJobEquipment($createData, $jobId, auth()->id());

        return FullJobEquipmentResponse::make($jobEquipment, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/equipment/{job_equipment_id}",
     *     tags={"Equipment", "Jobs"},
     *     summary="Returns full information about job equipment",
     *     description="Returns full information about job equipment. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="job_equipment_id",
     *         in="path",
     *         required=true,
     *         description="Job equipment identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullJobEquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param int $jobId
     * @param int $jobEquipmentId
     *
     * @return \App\Http\Responses\Jobs\FullJobEquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $jobId, int $jobEquipmentId)
    {
        $this->authorize('jobs.usage.view');

        $jobEquipment = $this->service->getJobEquipment($jobEquipmentId);

        return FullJobEquipmentResponse::make($jobEquipment);
    }

    /**
     * @OA\Patch(
     *     path="/jobs/{job_id}/equipment/{job_equipment_id}/finish-using",
     *     tags={"Equipment", "Jobs"},
     *     summary="Update ended_at field for existing job equipment",
     *     description="Allows to update ended_at field for existing job equipment. **`jobs.usage.equipment.update`**
    , **`jobs.usage.equipment.manage`** (if entry wasn't created by the current user) permission are
    required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="job_equipment_id",
     *         in="path",
     *         required=true,
     *         description="Job equipment identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/FinishJobEquipmentUsingRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullJobEquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. The job is closed or the job equipment is used on an approved invoice or job
     *         equipment ended date is incorrect.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param FinishJobEquipmentUsingRequest $request
     * @param int                            $jobId
     * @param int                            $jobEquipmentId
     *
     * @return \App\Http\Responses\Jobs\FullJobEquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function finishUsing(FinishJobEquipmentUsingRequest $request, int $jobId, int $jobEquipmentId)
    {
        $this->authorize('jobs.usage.equipment.update');
        $jobEquipment = $this->service->getJobEquipment($jobEquipmentId);
        $this->authorize('manage', $jobEquipment);

        $endedAt      = new Carbon($request->getEndedAt());
        $jobEquipment = $this->service->finishJobEquipmentUsing($jobEquipmentId, $endedAt);

        return FullJobEquipmentResponse::make($jobEquipment);
    }


    /**
     * @OA\Patch(
     *     path="/jobs/{job_id}/equipment/{job_equipment_id}/override",
     *     tags={"Equipment", "Jobs"},
     *     summary="Update intervals_count_override field for existing job equipment",
     *     description="Allows to update intervals_count_override field for existing
    job equipment. **`jobs.usage.equipment.update`** , **`jobs.usage.equipment.manage`**
    (if entry wasn't created by the current user) permission are required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="job_equipment_id",
     *         in="path",
     *         required=true,
     *         description="Job equipment identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/OverrideJobEquipmentIntervalsCountRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullJobEquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. The job is closed or the job equipment is used on an approved invoice.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param OverrideJobEquipmentIntervalsCountRequest $request
     * @param int                                       $jobId
     * @param int                                       $jobEquipmentId
     *
     * @return \App\Http\Responses\Jobs\FullJobEquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function overrideIntervalsCount(
        OverrideJobEquipmentIntervalsCountRequest $request,
        int $jobId,
        int $jobEquipmentId
    ) {
        $this->authorize('jobs.usage.equipment.update');
        $jobEquipment = $this->service->getJobEquipment($jobEquipmentId);
        $this->authorize('manage', $jobEquipment);

        $count        = $request->getIntervalsCountOverride();
        $jobEquipment = $this->service->overrideJobEquipmentIntervalsCount($jobEquipmentId, $count);

        return FullJobEquipmentResponse::make($jobEquipment);
    }

    /**
     * @OA\Delete(
     *     path="/jobs/{job_id}/equipment/{job_equipment_id}",
     *     tags={"Equipment", "Jobs"},
     *     summary="Delete existing job equipment",
     *     description="Delete existing job equipment. **`jobs.usage.equipment.delete`**
    , **`jobs.usage.equipment.manage`** (if entry wasn't created by the current user) permission are
    required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="job_equipment_id",
     *         in="path",
     *         required=true,
     *         description="Job equipment identifier.",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. The job is closed or the job equipment is used on an approved invoice.",
     *     ),
     * )
     *
     * @param int $jobId
     * @param int $jobEquipmentId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $jobId, int $jobEquipmentId)
    {
        $this->authorize('jobs.usage.equipment.delete');
        $jobEquipment = $this->service->getJobEquipment($jobEquipmentId);
        $this->authorize('manage', $jobEquipment);

        $this->service->deleteJobEquipment($jobEquipmentId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/equipment/amount",
     *     tags={"Equipment", "Jobs"},
     *     summary="Returns information about equipment costing for specific job",
     *     description="Returns information about equipment costing. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobEquipmentTotalAmountResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param int $jobId
     *
     * @return JobEquipmentTotalAmountResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTotalAmount(int $jobId)
    {
        $this->authorize('jobs.usage.view');

        $totalAmount = $this->service->getJobEquipmentTotalAmount($jobId);

        return JobEquipmentTotalAmountResponse::make($totalAmount);
    }
}
