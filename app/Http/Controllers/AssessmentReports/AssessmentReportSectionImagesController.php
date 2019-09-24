<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionImageData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionImagesService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionImageRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionImageRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionImageResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionImagesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionImagesController extends Controller
{
    /**
     * @var AssessmentReportSectionImagesService
     */
    protected $sectionImagesService;

    /**
     * AssessmentReportSectionImagesController constructor.
     *
     * @param AssessmentReportSectionImagesService $service
     */
    public function __construct(AssessmentReportSectionImagesService $service)
    {
        $this->sectionImagesService = $service;
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/images",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section image",
     *     description="Create new assessment report section image. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionImageRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionImageResponse")
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
     *         description="Not allowed. Assessment report is approved or it's type is non-image.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionImageRequest $request
     * @param int                                       $assessmentReportId
     * @param int                                       $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \JsonMapper_Exception
     */
    public function store(
        CreateAssessmentReportSectionImageRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportSectionImageData($request->validated());
        $image = $this->sectionImagesService->create($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionImageResponse::make($image, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/images/{image_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section image",
     *     description="Update existing assessment report section image. **`assessment_reports.manage`** permission
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
     *         name="image_id",
     *         in="path",
     *         required=true,
     *         description="AR section image identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionImageRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionImageResponse")
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
     * @param UpdateAssessmentReportSectionImageRequest $request
     * @param int                                       $assessmentReportId
     * @param int                                       $sectionId
     * @param int                                       $imageId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function update(
        UpdateAssessmentReportSectionImageRequest $request,
        int $assessmentReportId,
        int $sectionId,
        int $imageId
    ) {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportSectionImageData($request->validated());
        $image = $this->sectionImagesService->update($data, $assessmentReportId, $sectionId, $imageId);

        return AssessmentReportSectionImageResponse::make($image);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/images/{image_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section image",
     *     description="Delete existing assessment report section image. **`assessment_reports.manage`**
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
     *         name="image_id",
     *         in="path",
     *         required=true,
     *         description="AR section image identifier",
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
     * @param int $imageId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $sectionId, int $imageId)
    {
        $this->authorize('assessment_reports.manage');
        $this->sectionImagesService->delete($assessmentReportId, $sectionId, $imageId);

        return ApiOKResponse::make();
    }
}
