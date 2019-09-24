<?php

namespace App\Http\Controllers\Reporting;

use App\Components\Reporting\Models\Filters\ContactVolumeReportFilter;
use App\Components\Reporting\Services\ContactVolumeReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\ContactVolumeReportRequest;
use App\Http\Responses\Reporting\ContactVolumeReportResponse;

/**
 * Class ReportingContactsController
 *
 * @package App\Http\Controllers\Reporting
 */
class ReportingContactsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/contacts/reports/volume",
     *      tags={"Reporting"},
     *      summary="Returns contacts volume report data.",
     *      description="Returns report to show contact volume. **`contacts.reports.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Allows to filter by date. Entities with date greater or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Allows to filter by date. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="query",
     *         description="Allows to filter by contact id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="staff_id",
     *         in="query",
     *         description="Allows to filter by staff (user) id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         description="Allows to filter by tags ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactVolumeReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Reporting\ContactVolumeReportRequest $request
     *
     * @return ContactVolumeReportResponse
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function volumeReport(ContactVolumeReportRequest $request): ContactVolumeReportResponse
    {
        $this->authorize('contacts.reports.view');

        $filter = new ContactVolumeReportFilter($request->validated());

        $volumeService = app()->make(ContactVolumeReportService::class, ['filter' => $filter]);

        $reportData = $volumeService->getReportData();

        return ContactVolumeReportResponse::make($reportData->toArray());
    }
}
