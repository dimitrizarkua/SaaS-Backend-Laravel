<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobPhotosServiceInterfaces;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\AttachJobPhotoRequest;
use App\Http\Requests\Jobs\DetachJobPhotosRequest;
use App\Http\Requests\Jobs\UpdateJobPhotoRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobPhotosListResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * Class JobPhotosController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobPhotosController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobPhotosServiceInterfaces
     */
    protected $service;

    /**
     * JobPhotosController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobPhotosServiceInterfaces $service
     */
    public function __construct(JobPhotosServiceInterfaces $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{job_id}/photos",
     *      tags={"Jobs"},
     *      summary="List all the job photos",
     *      description="Allows to list all photos attached to the job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobPhotosListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested job could not be found.",
     *      ),
     * )
     *
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listJobPhotos(int $jobId)
    {
        $this->authorize('jobs.view');
        $jobPhotos = $this->service->listJobPhotos($jobId);

        return JobPhotosListResponse::make($jobPhotos);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/photos/{photo_id}",
     *      tags={"Jobs"},
     *      summary="Attach a photo to a job",
     *      description="Allows to attach a photo to a job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachJobPhotoRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="photo_id",
     *          in="path",
     *          required=true,
     *          description="Photo id",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Either job or photo could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Photo is already attached to the job or job is closed.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Jobs\AttachJobPhotoRequest $request
     * @param int                                           $jobId
     * @param int                                           $photoId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Photos\Exceptions\NotAllowedException
     */
    public function attachPhoto(AttachJobPhotoRequest $request, int $jobId, int $photoId)
    {
        $this->authorize('jobs.update');
        $this->service->attachPhoto($jobId, $photoId, Auth::id(), $request->getDescription());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/photos/{photo_id}",
     *      tags={"Jobs"},
     *      summary="Detach a photo from a job",
     *      description="Allows to detach a photo from a job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="photo_id",
     *          in="path",
     *          required=true,
     *          description="Photo id",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Either job or photo could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     *
     * @param int $jobId
     * @param int $photoId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Photos\Exceptions\NotAllowedException
     */
    public function detachPhoto(int $jobId, int $photoId)
    {
        $this->authorize('jobs.update');
        $this->service->detachPhoto($jobId, $photoId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/photos/bulk",
     *      tags={"Jobs"},
     *      summary="Detach photos from a job",
     *      description="Allows to detach multiple photos from a job. **jobs.update** permission is required to perform
     * this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="photo_ids",
     *                     type="array",
     *                     @OA\Items(type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Job could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      )
     * )
     *
     * @param \App\Http\Requests\Jobs\DetachJobPhotosRequest $request
     * @param int                                            $jobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Photos\Exceptions\NotAllowedException
     */
    public function detachPhotos(DetachJobPhotosRequest $request, int $jobId)
    {
        $this->authorize('jobs.update');
        $this->service->detachPhotos($jobId, $request->getPhotoIds());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{job_id}/photos/{photo_id}",
     *      tags={"Jobs"},
     *      summary="Update attached photo description",
     *      description="Allows to change description of an attached photo",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobPhotoRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="photo_id",
     *          in="path",
     *          required=true,
     *          description="Photo id",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
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
     *
     * @param \App\Http\Requests\Jobs\UpdateJobPhotoRequest $request
     * @param int                                           $jobId
     * @param int                                           $photoId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Photos\Exceptions\NotAllowedException
     */
    public function updatePhoto(UpdateJobPhotoRequest $request, int $jobId, int $photoId)
    {
        $this->authorize('jobs.update');
        $this->service->updateDescription($jobId, $photoId, Auth::id(), $request->getDescription());

        return ApiOKResponse::make();
    }
}
