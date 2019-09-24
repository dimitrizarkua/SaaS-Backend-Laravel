<?php

namespace App\Http\Controllers\Operations;

use App\Components\Locations\Models\Location;
use App\Components\Operations\Interfaces\StaffServiceInterface;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\ListStaffRequest;
use App\Http\Requests\Operations\SearchStaffRequest;
use App\Http\Requests\Operations\StaffRequest;
use App\Http\Responses\Operations\StaffListResponse;
use App\Http\Responses\Operations\StaffResponse;

/**
 * Class StaffController
 *
 * @package App\Http\Controllers\Operations
 */
class StaffController extends Controller
{
    /** @var \App\Components\Operations\Interfaces\StaffServiceInterface */
    private $service;

    /**
     * StaffController constructor.
     *
     * @param \App\Components\Operations\Interfaces\StaffServiceInterface $service
     */
    public function __construct(StaffServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/staff/search",
     *      tags={"Operations","Staff","Search"},
     *      summary="Search for staff",
     *      description="Search for staff. `operations.staff.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter staff by location id",
     *         required=true,
     *         @OA\Schema(
     *            type="integer",
     *            example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date on which the search is made",
     *         required=true,
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2018-01-01"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Allows to search staff by full name",
     *         required=true,
     *         @OA\Schema(
     *            type="string",
     *            example="John"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StaffListResponse")
     *      ),
     *      @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\SearchStaffRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchStaffRequest $request)
    {
        $this->authorize('operations.staff.view');

        $results = $this->service->searchForStaff(
            $request->getLocationId(),
            $request->getDate(),
            $request->getName()
        );

        return StaffListResponse::make($results);
    }

    /**
     * @OA\Get(
     *      path="/operations/staff",
     *      tags={"Operations","Staff"},
     *      summary="Get staff list",
     *      description="Get staff list. `operations.staff.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter staff by location id",
     *         required=true,
     *         @OA\Schema(
     *            type="integer",
     *            example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date on which the search is made",
     *         required=true,
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2018-01-01"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StaffListResponse")
     *      ),
     *      @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\ListStaffRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(ListStaffRequest $request)
    {
        $this->authorize('operations.staff.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Location::with('users')->findOrFail($request->getLocationId())
            ->users()
            ->paginate(Paginator::resolvePerPage());

        return StaffListResponse::make(
            $this->service->getUsersWithWorkHours($pagination->getItems(), $request->getDate()),
            $pagination->getPaginationData()
        );
    }

    /**
     * @OA\Get(
     *      path="/operations/staff/{staff_id}",
     *      tags={"Operations","Staff"},
     *      summary="Get specific staff",
     *      description="Get specific staff. `operations.staff.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter staff by location id",
     *         required=true,
     *         @OA\Schema(
     *            type="integer",
     *            example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date on which the search is made",
     *         required=true,
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2018-01-01"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StaffResponse")
     *      ),
     *      @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param int                                        $staffId
     * @param \App\Http\Requests\Operations\StaffRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $staffId, StaffRequest $request)
    {
        $this->authorize('operations.staff.view');

        $staff = $this->service->getStaff($staffId, $request->getDate());

        return StaffResponse::make($staff);
    }
}
