<?php

namespace App\Http\Controllers\Jobs;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Interfaces\AssessmentReportStatusWorkflowInterface;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\VO\AssessmentReportData;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\ChangeAssessmentReportStatusRequest;
use App\Http\Requests\AssessmentReports\CreateAssessmentReportRequest;
use App\Http\Requests\AssessmentReports\UpdateAssessmentReportRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportListResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportStatusAndTotalResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportStatusListResponse;
use App\Http\Responses\AssessmentReports\FullAssessmentReportResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JobAssessmentReportsController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobAssessmentReportsController extends Controller
{
    /**
     * @var AssessmentReportsServiceInterface
     */
    protected $assessmentReportsService;

    /**
     * @var AssessmentReportStatusWorkflowInterface
     */
    protected $statusService;

    /**
     * @var DocumentsServiceInterface
     */
    private $documentsService;

    /**
     * JobAssessmentReportsController constructor.
     *
     * @param AssessmentReportsServiceInterface       $assessmentReportsService
     * @param AssessmentReportStatusWorkflowInterface $statusService
     * @param DocumentsServiceInterface               $documentsService
     */
    public function __construct(
        AssessmentReportsServiceInterface $assessmentReportsService,
        AssessmentReportStatusWorkflowInterface $statusService,
        DocumentsServiceInterface $documentsService
    ) {
        $this->assessmentReportsService = $assessmentReportsService;
        $this->statusService            = $statusService;
        $this->documentsService         = $documentsService;
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/assessment-reports",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Get all assessment reports attached to the job",
     *     description="Get all assessment reports attached to the job. **`assessment_reports.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportListResponse")
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
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiOKResponse;
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(int $jobId)
    {
        $this->authorize('assessment_reports.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = AssessmentReport::whereJobId($jobId)
            ->orderByDesc('date')
            ->orderBy('heading')
            ->paginate(Paginator::resolvePerPage());

        return AssessmentReportListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/jobs/{job_id}/assessment-reports",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Create new assessment report",
     *     description="Create new assessment report. **`assessment_reports.manage`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateAssessmentReportRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportResponse")
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateAssessmentReportRequest $request
     * @param Job                           $job
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateAssessmentReportRequest $request, Job $job)
    {
        $this->authorize('assessment_reports.manage');
        $data             = new AssessmentReportData($request->validated());
        $assessmentReport = $this->assessmentReportsService->createAssessmentReport($data, $job->id, auth()->id());

        return AssessmentReportResponse::make($assessmentReport, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Get full information about specific assessment report",
     *     description="Get full information about specific assessment report. **`assessment_reports.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *         @OA\JsonContent(ref="#/components/schemas/FullAssessmentReportResponse")
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
     * @param int $jobId
     * @param int $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show(int $jobId, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.view');
        $assessmentReport = $this->assessmentReportsService->getFullAssessmentReport($assessmentReportId);

        return FullAssessmentReportResponse::make($assessmentReport);
    }

    /**
     * @OA\Patch(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Update existing assessment report",
     *     description="Update existing assessment report. **`assessment_reports.manage`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *             @OA\Schema(ref="#/components/schemas/UpdateAssessmentReportRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportResponse")
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
     * @param UpdateAssessmentReportRequest $request
     * @param int                           $jobId
     * @param int                           $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Throwable
     *
     */
    public function update(UpdateAssessmentReportRequest $request, int $jobId, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $data             = new AssessmentReportData($request->validated());
        $assessmentReport = $this->assessmentReportsService->updateAssessmentReport($data, $assessmentReportId);

        return AssessmentReportResponse::make($assessmentReport);
    }

    /**
     * @OA\Delete(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Delete existing assessment report",
     *     description="Delete existing assessment report. **`assessment_reports.manage`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     * @param int $jobId
     * @param int $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function destroy(int $jobId, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $this->assessmentReportsService->deleteAssessmentReport($assessmentReportId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}/next-statuses",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Get statuses that assessment report can be transitioned to",
     *     description="Returns list of status transitions that are possible for specific assessment
    report. **`assessment_reports.view`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportStatusListResponse")
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
     * @param int $jobId
     * @param int $assessmentReportId
     *
     * @return AssessmentReportStatusListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getNextStatuses(int $jobId, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.view');
        $assessmentReport = AssessmentReport::whereJobId($jobId)
            ->findOrFail($assessmentReportId);

        $statuses = $this->statusService->setAssessmentReport($assessmentReport)
            ->getNextStatuses();

        return new AssessmentReportStatusListResponse($statuses);
    }

    /**
     * @OA\Patch(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}/statuses",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Change assessment report status",
     *     description="Allows to change status of the assessment report. **`assessment_reports.manage`**
    permission, **`assessment_reports.manage_cancelled`** permission (if user change cancelled AR)
    , **`assessment_reports.approve`** permission (if user approve AR) are required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *             @OA\Schema(ref="#/components/schemas/ChangeAssessmentReportStatusRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
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
     *         description="Not allowed. Status could not be changed.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param ChangeAssessmentReportStatusRequest $request
     * @param int                                 $jobId
     * @param int                                 $assessmentReportId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeStatus(ChangeAssessmentReportStatusRequest $request, int $jobId, int $assessmentReportId)
    {
        $this->authorize('assessment_reports.manage');
        $assessmentReport = $this->assessmentReportsService->getAssessmentReport($assessmentReportId);
        $this->authorize('manageCancelled', $assessmentReport);
        if (AssessmentReportStatuses::CLIENT_APPROVED === $request->getStatus()) {
            $this->authorize('assessment_reports.approve');
        }

        $this->statusService->setAssessmentReport($assessmentReport)
            ->changeStatus($request->getStatus(), null, auth()->id());

        return ApiOkResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}/document",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Get printed version of specific assessment report",
     *     description="Get printed version of specific assessment report. **`assessment_reports.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="file")
     *        )
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
     * @param int              $jobId
     * @param AssessmentReport $assessmentReport
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function document(int $jobId, AssessmentReport $assessmentReport): Response
    {
        $this->authorize('assessment_reports.view');
        $documentId = $assessmentReport->document_id
            ?? $this->assessmentReportsService->generateDocument($assessmentReport->id);

        return $this->documentsService->getDocumentContentsAsResponse($documentId);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/assessment-reports/{assessment_report_id}/status-and-total",
     *     tags={"Jobs", "Assessment Reports"},
     *     summary="Get latest status and total amount of assessment report",
     *     description="Get latest status and total amount of specific assessment report
    . **`assessment_reports.view`** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
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
     *         @OA\JsonContent(ref="#/components/schemas/AssessmentReportStatusAndTotalResponse")
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
     * @param int $jobId
     * @param int $assessmentReportId
     *
     * @return AssessmentReportStatusAndTotalResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getStatusAndTotal(int $jobId, int $assessmentReportId): AssessmentReportStatusAndTotalResponse
    {
        $this->authorize('assessment_reports.view');

        $statusAndTotal = $this->assessmentReportsService->getStatusAndTotal($assessmentReportId);

        return AssessmentReportStatusAndTotalResponse::make($statusAndTotal);
    }
}
