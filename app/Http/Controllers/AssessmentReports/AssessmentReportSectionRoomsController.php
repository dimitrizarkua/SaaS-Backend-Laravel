<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionRoomData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionRoomsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportSectionRoomRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportSectionRoomRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionRoomResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionRoomsController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class AssessmentReportSectionRoomsController extends Controller
{
    /**
     * @var AssessmentReportSectionRoomsService
     */
    protected $sectionRoomsService;

    /**
     * AssessmentReportSectionRoomsController constructor.
     *
     * @param AssessmentReportSectionRoomsService $service
     */
    public function __construct(AssessmentReportSectionRoomsService $service)
    {
        $this->sectionRoomsService = $service;
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/rooms",
     *     tags={"Assessment Reports"},
     *     summary="Create new assessment report section room",
     *     description="Create new assessment report section room. **`assessment_reports.manage`** permission
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
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportSectionRoomRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionRoomResponse")
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
     *         description="Not allowed. Assessment report is approved or it's type is non-room.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportSectionRoomRequest $request
     * @param int                                      $assessmentReportId
     * @param int                                      $sectionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \JsonMapper_Exception
     */
    public function store(
        CreateAssessmentReportSectionRoomRequest $request,
        int $assessmentReportId,
        int $sectionId
    ) {
        $this->authorize('assessment_reports.manage');
        $data = new AssessmentReportSectionRoomData($request->validated());
        $room = $this->sectionRoomsService->create($data, $assessmentReportId, $sectionId);

        return AssessmentReportSectionRoomResponse::make($room, null, 201);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/rooms/{room_id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing assessment report section room",
     *     description="Update existing assessment report section room. **`assessment_reports.manage`** permission
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
     *         name="room_id",
     *         in="path",
     *         required=true,
     *         description="AR section room identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportSectionRoomRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportSectionRoomResponse")
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
     * @param UpdateAssessmentReportSectionRoomRequest $request
     * @param int                                      $assessmentReportId
     * @param int                                      $sectionId
     * @param int                                      $roomId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function update(
        UpdateAssessmentReportSectionRoomRequest $request,
        int $assessmentReportId,
        int $sectionId,
        int $roomId
    ) {
        $this->authorize('assessment_reports.manage');
        $data = new AssessmentReportSectionRoomData($request->validated());
        $room = $this->sectionRoomsService->update($data, $assessmentReportId, $sectionId, $roomId);

        return AssessmentReportSectionRoomResponse::make($room);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/{assessment_report_id}/sections/{section_id}/rooms/{room_id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing assessment report section room",
     *     description="Delete existing assessment report section room. **`assessment_reports.manage`**
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
     *         name="room_id",
     *         in="path",
     *         required=true,
     *         description="AR section room identifier",
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
     * @param int $roomId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(int $assessmentReportId, int $sectionId, int $roomId)
    {
        $this->authorize('assessment_reports.manage');
        $this->sectionRoomsService->delete($assessmentReportId, $sectionId, $roomId);

        return ApiOKResponse::make();
    }
}
