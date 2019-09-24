<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\AssessmentReportPrintVersion;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportCreated;
use App\Components\AssessmentReports\Events\AssessmentReportUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\VO\AssessmentReportData;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Utils\HtmlToPDFConverter;
use App\Utils\FileIO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Class AssessmentReportsService
 *
 * @package App\Components\AssessmentReports\Services
 */
class AssessmentReportsService implements AssessmentReportsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getAssessmentReport(int $assessmentReportId): AssessmentReport
    {
        return AssessmentReport::findOrFail($assessmentReportId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getFullAssessmentReport(int $assessmentReportId): AssessmentReport
    {
        return AssessmentReport::with([
            'latestStatus',
            'sections.textBlocks',
            'sections.image.photo',
            'sections.photos.photo',
            'sections.costItems.costItem.taxRate',
            'sections.room.flooringType',
            'sections.room.flooringSubtype',
            'sections.room.underlayType',
            'sections.room.nonRestorableReason',
            'sections.room.carpetType',
            'sections.room.carpetConstructionType',
            'sections.room.carpetAge',
            'sections.room.carpetFaceFibre',
            'user',
            'job',
        ])->findOrFail($assessmentReportId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createAssessmentReport(AssessmentReportData $data, int $jobId, int $userId): AssessmentReport
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = DB::transaction(function () use ($data, $jobId, $userId) {
            $assessmentReport          = new AssessmentReport($data->toArray());
            $assessmentReport->job_id  = $jobId;
            $assessmentReport->user_id = $userId;
            $assessmentReport->saveOrFail();

            $assessmentReport->changeStatus(AssessmentReportStatuses::DRAFT, $userId);

            return $assessmentReport;
        });

        event(new AssessmentReportCreated($assessmentReport->id));

        return $assessmentReport;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     */
    public function updateAssessmentReport(AssessmentReportData $data, int $assessmentReportId): AssessmentReport
    {
        $this->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $assessmentReport = $this->getAssessmentReport($assessmentReportId);
        $assessmentReport->update($data->toArray());

        event(new AssessmentReportUpdated($assessmentReport->id));

        return $assessmentReport;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function deleteAssessmentReport(int $assessmentReportId): void
    {
        $this->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        AssessmentReport::whereId($assessmentReportId)->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\AssessmentReports\Exceptions\NotAllowedException
     */
    public function throwExceptionIfAssessmentReportIsApprovedOrCancelled(int $assessmentReportId): void
    {
        $assessmentReport = $this->getAssessmentReport($assessmentReportId);

        if ($assessmentReport->isApproved()) {
            throw new NotAllowedException('Assessment report is approved.');
        }

        if ($assessmentReport->isCancelled()) {
            throw new NotAllowedException('Assessment report is cancelled.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function generateDocument(int $assessmentReportId): int
    {
        $documentService  = app()->make(DocumentsServiceInterface::class);
        $assessmentReport = $this->getFullAssessmentReport($assessmentReportId);
        $documentId       = $assessmentReport->document_id;

        $fileName  = $assessmentReport->generatePDFName();
        $filePath  = FileIO::getTmpFilePath($fileName);
        $viewData  = new AssessmentReportPrintVersion($assessmentReport);
        $converter = new HtmlToPDFConverter($viewData, AssessmentReport::PRINT_VIEW);
        $converter->convert($filePath);

        $fileInstance = new UploadedFile($filePath, $fileName);
        if (null === $documentId) {
            // Create a new document and attach it to the assessment report
            $document                      = $documentService->createDocumentFromFile($fileInstance);
            $assessmentReport->document_id = $document->id;
            $assessmentReport->saveOrFail();
        } else {
            // Update existing document
            $document = $documentService->updateDocumentContentsFromFile($documentId, $fileInstance);
        }

        return $document->id;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getStatusAndTotal(int $assessmentReportId): array
    {
        $assessmentReport = AssessmentReport::with([
            'latestStatus',
            'costItems.taxRate',
        ])
            ->findOrFail($assessmentReportId);

        return [
            'status' => $assessmentReport->latestStatus->status,
            'total'  => $assessmentReport->getTotalAmount(),
        ];
    }
}
