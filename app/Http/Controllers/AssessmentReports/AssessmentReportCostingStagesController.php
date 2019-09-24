<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportCostingStageData;
use App\Components\AssessmentReports\Services\AssessmentReportCostingStagesService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportCostingStageRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportCostingStageRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportCostingStageResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportCostingStageListResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostingStagesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportCostingStagesController extends Controller
{
    /**
     * @var AssessmentReportCostingStagesService
     */
    protected $costingStagesService;

    /**
     * AssessmentReportCostingStagesController constructor.
     *
     * @param AssessmentReportCostingStagesService $service
     */
    public function __construct(AssessmentReportCostingStagesService $service)
    {
        $this->costingStagesService = $service;
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/costing-stages",
     *     tags={"Assessment Reports"},
     *     summary="Get all costing stages attached to the assessment report",
     *     description="Get all costing stages attached to the assessment report. **`assessment_reports.view`**
    permission is required to perform this operation.",
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
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostingStageListResponse")
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
        $stages = $this->costingStagesService->getEntities($assessmentReportId);

        return AssessmentReportCostingStageListResponse::make($stages);
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/costing-stages",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report costing stage",
     *     description="Create new assessment report costing stage. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportCostingStageRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostingStageResponse")
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
     * @param CreateAssessmentReportCostingStageRequest $request
     * @param int                                       $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function store(CreateAssessmentReportCostingStageRequest $request, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportCostingStageData($request->validated());
        $stage = $this->costingStagesService->create($data, $assessmentReportId);

        return AssessmentReportCostingStageResponse::make($stage, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/costing-stages/{costing_stage_id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific assessment report costing stage",
     *     description="Get full information about specific assessment report costing
    stage. **`assessment_reports.view`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="costing_stage_id",
     *         in="path",
     *         required=true,
     *         description="AR costing stage identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostingStageResponse")
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
     * @param int $stageId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $assessmentReportId, int $stageId)
    {
        $this->authorize('assessment_reports.view');
        $stage = $this->costingStagesService->getEntity($assessmentReportId, $stageId);

        return AssessmentReportCostingStageResponse::make($stage);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/costing-stages/{costing_stage_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report costing stage",
     *     description="Update existing assessment report costing stage. **`assessment_reports.manage`** permission
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
     *         name="costing_stage_id",
     *         in="path",
     *         required=true,
     *         description="AR costing stage identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportCostingStageRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostingStageResponse")
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
     * @param UpdateAssessmentReportCostingStageRequest $request
     * @param int                                       $assessmentReportId
     * @param int                                       $stageId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function update(UpdateAssessmentReportCostingStageRequest $request, int $assessmentReportId, int $stageId)
    {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportCostingStageData($request->validated());
        $stage = $this->costingStagesService->update($data, $assessmentReportId, $stageId);

        return AssessmentReportCostingStageResponse::make($stage);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/costing-stages/{costing_stage_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report costing stage",
     *     description="Delete existing assessment report costing stage. **`assessment_reports.manage`** permission is
    required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="costing_stage_id",
     *         in="path",
     *         required=true,
     *         description="AR costing stage identifier",
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
     * @param int $stageId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $stageId)
    {
        $this->authorize('assessment_reports.manage');
        $this->costingStagesService->delete($assessmentReportId, $stageId);

        return ApiOKResponse::make();
    }
}
