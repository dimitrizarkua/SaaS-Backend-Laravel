<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionListResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionsController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionsController extends Controller
{
    /**
     * @var AssessmentReportSectionsService
     */
    protected $sectionsService;

    /**
     * AssessmentReportSectionsController constructor.
     *
     * @param AssessmentReportSectionsService $service
     */
    public function __construct(AssessmentReportSectionsService $service)
    {
        $this->sectionsService = $service;
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/sections",
     *     tags={"Assessment Reports"},
     *     summary="Get all sections attached to the assessment report",
     *     description="Get all sections attached to the assessment report. **`assessment_reports.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionListResponse")
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
     *         description="Not found. Requested resource could not be found.",
     *     ),
     * )
     * @param int $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse;
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(int $assessmentReportId)
    {
        $this->authorize('assessment_reports.view');
        $sections = $this->sectionsService->getEntities($assessmentReportId);

        return AssessmentReportSectionListResponse::make($sections);
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section",
     *     description="Create new assessment report section. **`assessment_reports.manage`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionResponse")
     *      ),
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
     *         description="Not found. Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. Assessment report is approved.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionRequest $request
     * @param int                                  $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function store(CreateAssessmentReportSectionRequest $request, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $data    = new AssessmentReportSectionData($request->validated());
        $section = $this->sectionsService->create($data, $assessmentReportId);

        return AssessmentReportSectionResponse::make($section, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific assessment report section",
     *     description="Get full information about specific assessment report section. **`assessment_reports.view`**
    permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="path",
     *         required=true,
     *         description="AR section identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionResponse")
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
     *         description="Not found. Requested resource could not be found.",
     *     ),
     * )
     * @param int $assessmentReportId
     * @param int $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $assessmentReportId, int $sectionId)
    {
        $this->authorize('assessment_reports.view');
        $section = $this->sectionsService->getEntity($assessmentReportId, $sectionId);

        return AssessmentReportSectionResponse::make($section);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section",
     *     description="Update existing assessment report section. **`assessment_reports.manage`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="path",
     *         required=true,
     *         description="AR section identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionResponse")
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
     *         description="Not found. Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. Assessment report is approved.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param UpdateAssessmentReportSectionRequest $request
     * @param int                                  $assessmentReportId
     * @param int                                  $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function update(UpdateAssessmentReportSectionRequest $request, int $assessmentReportId, int $sectionId)
    {
        $this->authorize('assessment_reports.manage');
        $data    = new AssessmentReportSectionData($request->validated());
        $section = $this->sectionsService->update($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionResponse::make($section);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section",
     *     description="Delete existing assessment report section. **`assessment_reports.manage`** permission is
     *     required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="path",
     *         required=true,
     *         description="AR section identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
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
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. Assessment report is approved.",
     *     ),
     * )
     * @param int $assessmentReportId
     * @param int $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $sectionId)
    {
        $this->authorize('assessment_reports.manage');
        $this->sectionsService->delete($assessmentReportId, $sectionId);

        return ApiOKResponse::make();
    }
}
