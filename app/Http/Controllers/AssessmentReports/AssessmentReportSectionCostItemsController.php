<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionCostItemData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionCostItemsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionCostItemRequest;
use App\Http\Requests\AssessmentReports\DeleteAssessmentReportSectionCostItemRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionCostItemRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionCostItemResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionCostItemsController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionCostItemsController extends Controller
{
    /**
     * @var AssessmentReportSectionCostItemsService
     */
    protected $sectionCostItemsService;

    /**
     * AssessmentReportSectionCostItemsController constructor.
     *
     * @param AssessmentReportSectionCostItemsService $service
     */
    public function __construct(AssessmentReportSectionCostItemsService $service)
    {
        $this->sectionCostItemsService = $service;
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/cost-items",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section cost item",
     *     description="Create new assessment report section cost item. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionCostItemRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionCostItemResponse")
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
     *         description="Not allowed. Assessment report is approved or it's type is non-costs.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionCostItemRequest $request
     * @param int                                          $assessmentReportId
     * @param int                                          $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \JsonMapper_Exception
     */
    public function store(
        CreateAssessmentReportSectionCostItemRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $data     = new AssessmentReportSectionCostItemData($request->validated());
        $costItem = $this->sectionCostItemsService->create($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionCostItemResponse::make($costItem, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/cost-items",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section cost item",
     *     description="Update existing assessment report section cost item. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionCostItemRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionCostItemResponse")
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
     * @param UpdateAssessmentReportSectionCostItemRequest $request
     * @param int                                          $assessmentReportId
     * @param int                                          $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function update(
        UpdateAssessmentReportSectionCostItemRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $costItemId = $request->getCostItemId();
        $data       = new AssessmentReportSectionCostItemData($request->validated());
        $costItem   = $this->sectionCostItemsService->update($data, $assessmentReportId, $sectionId, $costItemId);

        return AssessmentReportSectionCostItemResponse::make($costItem);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/cost-items",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section cost item",
     *     description="Delete existing assessment report section cost item. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/DeleteAssessmentReportSectionCostItemRequest")
     *         )
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
     * @param DeleteAssessmentReportSectionCostItemRequest $request
     * @param int                                          $assessmentReportId
     * @param int                                          $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(
        DeleteAssessmentReportSectionCostItemRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $costItemId = $request->getCostItemId();
        $this->sectionCostItemsService->delete($assessmentReportId, $sectionId, $costItemId);

        return ApiOKResponse::make();
    }
}
