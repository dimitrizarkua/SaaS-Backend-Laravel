<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobMaterialsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\VO\JobMaterialData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobMaterialRequest;
use App\Http\Requests\Jobs\UpdateJobMaterialRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobMaterialResponse;
use App\Http\Responses\Jobs\JobMaterialsTotalAmountResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class JobMaterialsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobMaterialsController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobMaterialsServiceInterface
     */
    protected $service;

    /**
     * JobMaterialsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobMaterialsServiceInterface $service
     */
    public function __construct(JobMaterialsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/materials",
     *      tags={"Jobs", "Materials"},
     *      summary="Returns list job materials assigned to job.",
     *      description="Returns list job materials assigned to job. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMaterialResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Job $job)
    {
        $this->authorize('jobs.usage.view');

        $jobMaterials = JobMaterial::whereHas('job', function (Builder $query) use ($job) {
            $query->where('id', $job->id);
        })->get();

        return JobMaterialResponse::make($jobMaterials);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/materials",
     *      tags={"Jobs", "Materials"},
     *      summary="Create new job material.",
     *      description="Create new job material. **`jobs.usage.materials.create`** permission is required to
    perform this operation.",
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
     *              @OA\Schema(ref="#/components/schemas/CreateJobMaterialRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMaterialResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobMaterialRequest $request
     *
     * @param \App\Components\Jobs\Models\Job                  $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function store(CreateJobMaterialRequest $request, Job $job)
    {
        $this->authorize('jobs.usage.materials.create');

        /** @var JobMaterialData $data */
        $data        = new JobMaterialData($request->validated());
        $jobMaterial = $this->service->create($data);

        return JobMaterialResponse::make($jobMaterial, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/materials/{job_material_id}",
     *      tags={"Jobs", "Materials"},
     *      summary="Returns information about specific job material.",
     *      description="Returns information about specific job material. **`jobs.usage.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_material_id",
     *          in="path",
     *          required=true,
     *          description="Job material identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMaterialResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobMaterialId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Job $job, int $jobMaterialId)
    {
        $this->authorize('jobs.usage.view');

        return JobMaterialResponse::make(JobMaterial::findOrFail($jobMaterialId));
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/materials/{job_material_id}",
     *      tags={"Jobs", "Materials"},
     *      summary="Allows to update specific job material.",
     *      description="Allows to update specific job material. **`jobs.usage.materials.update`** permission is
    required to perform this operation. In order to manage materials entries that were not created
    by the current user the additionally **jobs.usage.materials.manage** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_material_id",
     *          in="path",
     *          required=true,
     *          description="Job material identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobMaterialRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMaterialResponse")
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
     * @param \App\Http\Requests\Jobs\UpdateJobMaterialRequest $request
     * @param \App\Components\Jobs\Models\Job                  $job
     * @param int                                              $jobMaterialId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function update(UpdateJobMaterialRequest $request, Job $job, int $jobMaterialId)
    {
        $this->authorize('jobs.usage.materials.update');
        $material = JobMaterial::findOrFail($jobMaterialId);
        if ($material->creator_id !== Auth::id()) {
            $this->authorize('jobs.usage.materials.manage');
        }

        /** @var JobMaterialData $data */
        $data        = new JobMaterialData($request->validated());
        $jobMaterial = $this->service->update($material, $data);

        return JobMaterialResponse::make($jobMaterial);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/materials/{job_material_id}",
     *      tags={"Jobs", "Materials"},
     *      summary="Delete existing job material.",
     *      description="Delete existing job material. **`jobs.usage.materials.delete`** permission is required to
    perform this operation. In order to manage materials entries that were not created
    by the current user the additionally **jobs.usage.materials.manage** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="job_material_id",
     *          in="path",
     *          required=true,
     *          description="Job material identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     * @param int                             $jobMaterialId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Job $job, int $jobMaterialId)
    {
        $this->authorize('jobs.usage.materials.delete');

        $material = JobMaterial::findOrFail($jobMaterialId);
        if ($material->creator_id !== Auth::id()) {
            $this->authorize('jobs.usage.materials.manage');
        }

        $this->service->delete($material);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/materials/amount",
     *      tags={"Jobs", "Materials"},
     *      summary="Returns total amount of job materials assigned to job.",
     *      description="Returns total amount of job materials assigned to job. **`jobs.usage.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMaterialsTotalAmountResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTotalAmount(Job $job)
    {
        $this->authorize('jobs.usage.view');

        $amounts = $this->service->calculateTotalAmountByJob($job->id);

        return JobMaterialsTotalAmountResponse::make($amounts);
    }
}
