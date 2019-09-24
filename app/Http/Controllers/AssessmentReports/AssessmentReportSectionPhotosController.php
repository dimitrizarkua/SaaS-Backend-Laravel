<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionPhotoData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionPhotosService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionPhotoRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionPhotoRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionPhotoResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionPhotosController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionPhotosController extends Controller
{
    /**
     * @var AssessmentReportSectionPhotosService
     */
    protected $sectionPhotosService;

    /**
     * AssessmentReportSectionPhotosController constructor.
     *
     * @param AssessmentReportSectionPhotosService $service
     */
    public function __construct(AssessmentReportSectionPhotosService $service)
    {
        $this->sectionPhotosService = $service;
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/photos",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section photo",
     *     description="Create new assessment report section photo. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionPhotoRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionPhotoResponse")
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
     *         description="Not allowed. Assessment report is approved or it's type is non-photos.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionPhotoRequest $request
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
        CreateAssessmentReportSectionPhotoRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportSectionPhotoData($request->validated());
        $photo = $this->sectionPhotosService->create($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionPhotoResponse::make($photo, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/photos/{photo_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section photo",
     *     description="Update existing assessment report section photo. **`assessment_reports.manage`** permission
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
     *         name="photo_id",
     *         in="path",
     *         required=true,
     *         description="AR section photo identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionPhotoRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionPhotoResponse")
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
     * @param UpdateAssessmentReportSectionPhotoRequest $request
     * @param int                                       $assessmentReportId
     * @param int                                       $sectionId
     * @param int                                       $photoId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function update(
        UpdateAssessmentReportSectionPhotoRequest $request,
        int $assessmentReportId,
        int $sectionId,
        int $photoId
    ) {
        $this->authorize('assessment_reports.manage');
        $data  = new AssessmentReportSectionPhotoData($request->validated());
        $photo = $this->sectionPhotosService->update($data, $assessmentReportId, $sectionId, $photoId);

        return AssessmentReportSectionPhotoResponse::make($photo);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/photos/{photo_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section photo",
     *     description="Delete existing assessment report section photo. **`assessment_reports.manage`**
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
     *         name="photo_id",
     *         in="path",
     *         required=true,
     *         description="AR section photo identifier",
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
     * @param int $photoId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $sectionId, int $photoId)
    {
        $this->authorize('assessment_reports.manage');
        $this->sectionPhotosService->delete($assessmentReportId, $sectionId, $photoId);

        return ApiOKResponse::make();
    }
}
