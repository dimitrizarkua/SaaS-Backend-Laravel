<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionTextBlockData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionTextBlocksService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionTextBlockRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionTextBlockRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionTextBlockResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionTextBlocksController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionTextBlocksController extends Controller
{
    /**
     * @var AssessmentReportSectionTextBlocksService
     */
    protected $sectionTextBlocksService;

    /**
     * AssessmentReportSectionTextBlocksController constructor.
     *
     * @param AssessmentReportSectionTextBlocksService $service
     */
    public function __construct(AssessmentReportSectionTextBlocksService $service)
    {
        $this->sectionTextBlocksService = $service;
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/text-blocks",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section text block",
     *     description="Create new assessment report section text block. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionTextBlockRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionTextBlockResponse")
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
     *         description="Not allowed. Assessment report is approved or it's type is non-text.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionTextBlockRequest $request
     * @param int                                           $assessmentReportId
     * @param int                                           $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \JsonMapper_Exception
     */
    public function store(
        CreateAssessmentReportSectionTextBlockRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $data      = new AssessmentReportSectionTextBlockData($request->validated());
        $textBlock = $this->sectionTextBlocksService->create($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionTextBlockResponse::make($textBlock, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/text-blocks/{text_block_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section text block",
     *     description="Update existing assessment report section text block. **`assessment_reports.manage`** permission
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
     *     @OA\Parameter(
     *         name="text_block_id",
     *         in="path",
     *         required=true,
     *         description="AR section text block identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionTextBlockRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionTextBlockResponse")
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
     * @param UpdateAssessmentReportSectionTextBlockRequest $request
     * @param int                                           $assessmentReportId
     * @param int                                           $sectionId
     * @param int                                           $textBlockId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function update(
        UpdateAssessmentReportSectionTextBlockRequest $request,
        int $assessmentReportId,
        int $sectionId,
        int $textBlockId
    ) {
        $this->authorize('assessment_reports.manage');
        $data      = new AssessmentReportSectionTextBlockData($request->validated());
        $textBlock = $this->sectionTextBlocksService->update($data, $assessmentReportId, $sectionId, $textBlockId);

        return AssessmentReportSectionTextBlockResponse::make($textBlock);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/text-blocks/{text_block_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section text block",
     *     description="Delete existing assessment report section text block. **`assessment_reports.manage`**
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
     *     @OA\Parameter(
     *         name="text_block_id",
     *         in="path",
     *         required=true,
     *         description="AR section text block identifier",
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
     * @param int $textBlockId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $sectionId, int $textBlockId)
    {
        $this->authorize('assessment_reports.manage');
        $this->sectionTextBlocksService->delete($assessmentReportId, $sectionId, $textBlockId);

        return ApiOKResponse::make();
    }
}
