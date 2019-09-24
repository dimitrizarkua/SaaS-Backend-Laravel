<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\JobNotesTemplate;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateJobNotesTemplateRequest;
use App\Http\Requests\Jobs\GetJobNotesTemplatesRequest;
use App\Http\Requests\Jobs\UpdateJobNotesTemplateRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobNotesTemplateListResponse;
use App\Http\Responses\Jobs\JobNotesTemplateResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesTemplatesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobNotesTemplatesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/message-templates",
     *      tags={"Jobs"},
     *      summary="Get job notes templates",
     *      description="Returns list of job notes templates",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="active",
     *         in="path",
     *         description="If specified, allows to select only active/inactive templates.
    By default all the templates are included in selection.",
     *         @OA\Schema(
     *            type="boolean",
     *            example=true,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesTemplateListResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\GetJobNotesTemplatesRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GetJobNotesTemplatesRequest $request)
    {
        $this->authorize('jobs.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = JobNotesTemplate::search($request->validated())->paginate(Paginator::resolvePerPage());

        return JobNotesTemplateListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/jobs/message-templates",
     *      tags={"Jobs"},
     *      summary="Create new job notes template",
     *      description="Allows to create new job notes template",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateJobNotesTemplateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesTemplateResponse")
     *       ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Notes template with the same name already exists.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateJobNotesTemplateRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateJobNotesTemplateRequest $request)
    {
        $this->authorize('jobs.update');

        $template = JobNotesTemplate::create($request->validated());
        $template->saveOrFail();

        return JobNotesTemplateResponse::make($template, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/jobs/message-templates/{id}",
     *      tags={"Jobs"},
     *      summary="Get full info about job notes template",
     *      description="Retrieve full information about specific job notes template",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesTemplateResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     * )
     * @param \App\Components\Jobs\Models\JobNotesTemplate $messageTemplate
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(JobNotesTemplate $messageTemplate)
    {
        $this->authorize('jobs.view');

        return JobNotesTemplateResponse::make($messageTemplate);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/message-templates/{id}",
     *      tags={"Jobs"},
     *      summary="Update existing job notes template",
     *      description="Allows to update existing job notes template",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateJobNotesTemplateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobNotesTemplateResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\UpdateJobNotesTemplateRequest $request
     * @param \App\Components\Jobs\Models\JobNotesTemplate          $messageTemplate
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateJobNotesTemplateRequest $request, JobNotesTemplate $messageTemplate)
    {
        $this->authorize('jobs.update');

        $messageTemplate->fillFromRequest($request);

        return JobNotesTemplateResponse::make($messageTemplate);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/message-templates/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing job notes template",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Jobs\Models\JobNotesTemplate $messageTemplate
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(JobNotesTemplate $messageTemplate)
    {
        $this->authorize('jobs.update');

        $messageTemplate->delete();

        return ApiOKResponse::make();
    }
}
