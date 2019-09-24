<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobTagsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Tags\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Tags\TagListResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobTagsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobTagsController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobNotesServiceInterface
     */
    protected $service;

    /**
     * JobTagsController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobTagsServiceInterface $service
     */
    public function __construct(JobTagsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/tags",
     *      tags={"Jobs"},
     *      summary="Returns list of tags assigned to job.",
     *      description="Allows to view list of job tags.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listJobTags(Job $job)
    {
        $this->authorize('jobs.view');

        return TagListResponse::make($job->tags);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/tags/{tag_id}",
     *      tags={"Jobs"},
     *      summary="Assign a tag to a job.",
     *      description="Allows to assign tag to a job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          required=true,
     *          description="Tag identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or tag doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Requested tag already assigned to specified job or job is closed.",
     *      ),
     * )
     * @param Job $job
     * @param Tag $tag
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function tagJob(Job $job, Tag $tag)
    {
        $this->authorize('jobs.manage_tags');

        $this->service->assignTag($job->id, $tag->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/tags/{tag_id}",
     *      tags={"Jobs"},
     *      summary="Unassign ta ag from a job.",
     *      description="Allows to unassign a tag from a job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          required=true,
     *          description="Tag identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or tag doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the closed or cancelled job.",
     *      ),
     * )
     * @param Job $job
     * @param Tag $tag
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function untagJob(Job $job, Tag $tag)
    {
        $this->authorize('jobs.manage_tags');

        $this->service->unassignTag($job->id, $tag->id);

        return ApiOKResponse::make();
    }
}
