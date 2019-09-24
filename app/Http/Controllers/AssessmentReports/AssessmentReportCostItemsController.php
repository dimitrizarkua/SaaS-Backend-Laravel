<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportCostItemData;
use App\Components\AssessmentReports\Services\AssessmentReportCostItemsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportCostItemRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportCostItemRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportCostItemResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportCostItemListResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostItemsController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportCostItemsController extends Controller
{
    /**
     * @var AssessmentReportCostItemsService
     */
    protected $costItemsService;

    /**
     * AssessmentReportCostItemsController constructor.
     *
     * @param AssessmentReportCostItemsService $service
     */
    public function __construct(AssessmentReportCostItemsService $service)
    {
        $this->costItemsService = $service;
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/cost-items",
     *     tags={"Assessment Reports"},
     *     summary="Get all cost items attached to the assessment report",
     *     description="Get all cost items attached to the assessment report. **`assessment_reports.view`**
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
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostItemListResponse")
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
        $items = $this->costItemsService->getEntities($assessmentReportId);

        return AssessmentReportCostItemListResponse::make($items);
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/cost-items",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report cost item",
     *     description="Create new assessment report cost item. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportCostItemRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostItemResponse")
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
     * @param CreateAssessmentReportCostItemRequest $request
     * @param int                                   $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function store(CreateAssessmentReportCostItemRequest $request, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $data = new AssessmentReportCostItemData($request->validated());
        $item = $this->costItemsService->create($data, $assessmentReportId);

        return AssessmentReportCostItemResponse::make($item, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/{assessment_report_id}/cost-items/{cost_item_id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific assessment report cost item",
     *     description="Get full information about specific assessment report cost
    item. **`assessment_reports.view`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="assessment_report_id",
     *         in="path",
     *         required=true,
     *         description="AR identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="cost_item_id",
     *         in="path",
     *         required=true,
     *         description="AR cost item identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostItemResponse")
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
     * @param int $itemId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $assessmentReportId, int $itemId)
    {
        $this->authorize('assessment_reports.view');
        $item = $this->costItemsService->getEntity($assessmentReportId, $itemId);

        return AssessmentReportCostItemResponse::make($item);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/cost-items/{cost_item_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report cost item",
     *     description="Update existing assessment report cost item. **`assessment_reports.manage`** permission
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
     *         name="cost_item_id",
     *         in="path",
     *         required=true,
     *         description="AR cost item identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportCostItemRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportCostItemResponse")
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
     * @param UpdateAssessmentReportCostItemRequest $request
     * @param int                                   $assessmentReportId
     * @param int                                   $itemId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function update(UpdateAssessmentReportCostItemRequest $request, int $assessmentReportId, int $itemId)
    {
        $this->authorize('assessment_reports.manage');
        $data = new AssessmentReportCostItemData($request->validated());
        $item = $this->costItemsService->update($data, $assessmentReportId, $itemId);

        return AssessmentReportCostItemResponse::make($item);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/cost-items/{cost_item_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report cost item",
     *     description="Delete existing assessment report cost item. **`assessment_reports.manage`** permission is
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
     *         name="cost_item_id",
     *         in="path",
     *         required=true,
     *         description="AR cost item identifier",
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
     * @param int $itemId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $itemId)
    {
        $this->authorize('assessment_reports.manage');
        $this->costItemsService->delete($assessmentReportId, $itemId);

        return ApiOKResponse::make();
    }
}
