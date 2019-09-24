<?php

namespace App\Http\Controllers\Operations;

use App\Components\Operations\Interfaces\RunTemplatesServiceInterface;
use App\Components\Operations\Models\JobRunTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CreateRunTemplateRequest;
use App\Http\Requests\Operations\ListRunTemplatesRequest;
use App\Http\Requests\Operations\UpdateRunTemplateRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Operations\FullRunTemplateResponse;
use App\Http\Responses\Operations\RunTemplateListResponse;
use App\Http\Responses\Operations\RunTemplateResponse;

/**
 * Class RunTemplatesController
 *
 * @package App\Http\Controllers\Operations
 */
class RunTemplatesController extends Controller
{
    /** @var \App\Components\Operations\Interfaces\RunTemplatesServiceInterface $service */
    private $service;

    /**
     * VehiclesController constructor.
     *
     * @param \App\Components\Operations\Interfaces\RunTemplatesServiceInterface $service
     */
    public function __construct(RunTemplatesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/runs/templates",
     *      tags={"Operations"},
     *      summary="Returns list of all location's run templates",
     *      description="Allows to retrieve run templates assigned to the specified location.
                        `operations.runs_templates.view` permission is required to perform this operation",
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
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateListResponse")
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
     * @param \App\Http\Requests\Operations\ListRunTemplatesRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listLocationTemplates(ListRunTemplatesRequest $request)
    {
        $this->authorize('operations.runs_templates.view');

        $templates = $this->service->listLocationTemplates($request->getLocationId());

        return RunTemplateListResponse::make($templates);
    }

    /**
     * @OA\Get(
     *      path="/operations/runs/templates/{id}",
     *      tags={"Operations"},
     *      summary="Retrieve information about specific run template",
     *      description="Allows to retrieve information about specific run template.
                        `operations.runs_templates.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullRunTemplateResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Operations\Models\JobRunTemplate $template
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(JobRunTemplate $template)
    {
        $this->authorize('operations.runs_templates.view');

        return FullRunTemplateResponse::make($template);
    }

    /**
     * @OA\Post(
     *      path="/operations/runs/templates",
     *      tags={"Operations"},
     *      summary="Create new run template",
     *      description="Allows to create new run template for the specified location.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateRunTemplateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateRunTemplateRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateRunTemplateRequest $request)
    {
        $this->authorize('operations.runs_templates.manage');

        $template = $this->service->createTemplate(
            $request->getLocationId(),
            $request->getName()
        );

        return RunTemplateResponse::make($template, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/operations/runs/templates/{id}",
     *      tags={"Operations"},
     *      summary="Update existing run template",
     *      description="Allows to update existing run template.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateRunTemplateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/RunTemplateResponse")
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
     * @param \App\Http\Requests\Operations\UpdateRunTemplateRequest $request
     * @param \App\Components\Operations\Models\JobRunTemplate       $template
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateRunTemplateRequest $request, JobRunTemplate $template)
    {
        $this->authorize('operations.runs_templates.manage');

        $template->fillFromRequest($request);

        return RunTemplateResponse::make($template);
    }

    /**
     * @OA\Delete(
     *      path="/operations/runs/templates/{id}",
     *      tags={"Operations"},
     *      summary="Delete existing template",
     *      description="Allows to delete existing template.
                        `operations.runs_templates.manage` permission is required to perform this operation",
     *      security={{"passport": {}}},
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
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $templateId)
    {
        $this->authorize('operations.runs_templates.manage');

        $this->service->deleteTemplate($templateId);

        return ApiOKResponse::make();
    }
}
