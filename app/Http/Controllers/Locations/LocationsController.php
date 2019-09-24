<?php

namespace App\Http\Controllers\Locations;

use App\Components\Addresses\Models\Suburb;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Locations\Models\Location;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\AddUserToLocationRequest;
use App\Http\Requests\Locations\CreateLocationRequest;
use App\Http\Requests\Locations\UpdateLocationRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\AccountingOrganizationResponse;
use App\Http\Responses\Locations\LocationResponse;
use App\Http\Responses\Locations\LocationsListResponse;
use App\Http\Responses\Locations\LocationSuburbsResponse;
use App\Http\Responses\Locations\LocationUsersResponse;
use App\Models\User;

/**
 * Class LocationsController
 *
 * @package App\Http\Controllers\Locations
 */
class LocationsController extends Controller
{
    /**
     * @var LocationsServiceInterface
     */
    private $locationsService;

    /**
     * @var AccountingOrganizationsServiceInterface
     */
    private $accountingOrganizationService;

    /**
     * LocationsController constructor.
     *
     * @param LocationsServiceInterface $locationsService
     */
    public function __construct(
        LocationsServiceInterface $locationsService,
        AccountingOrganizationsServiceInterface $accountingOrganizationService
    ) {
        $this->locationsService              = $locationsService;
        $this->accountingOrganizationService = $accountingOrganizationService;
    }

    /**
     * @OA\Get(
     *      path="/locations",
     *      tags={"Locations"},
     *      summary="List all locations",
     *      description="Returns list of all locations in the system",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationsListResponse")
     *       ),
     * )
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('locations.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Location::paginate(Paginator::resolvePerPage());

        return LocationsListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/locations/{id}",
     *      tags={"Locations"},
     *      summary="Get specific location info",
     *      description="Returns info about specific location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Locations\Models\Location $location
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Location $location)
    {
        $this->authorize('locations.view');

        return LocationResponse::make($location);
    }

    /**
     * @OA\Post(
     *      path="/locations",
     *      tags={"Locations"},
     *      summary="Allows to create new location",
     *      description="Allows to create new location",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateLocationRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param CreateLocationRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateLocationRequest $request)
    {
        $this->authorize('locations.create');

        $location = Location::create($request->validated());

        return LocationResponse::make($location, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/locations/{id}",
     *      tags={"Locations"},
     *      summary="Allows to update specific location",
     *      description="Allows to update specific location",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateLocationRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param UpdateLocationRequest $request
     * @param int                   $locationId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateLocationRequest $request, int $locationId)
    {
        $this->authorize('locations.update');

        $location = Location::findOrFail($locationId);
        $location->fillFromRequest($request);

        return LocationResponse::make($location);
    }

    /**
     * @OA\Get(
     *      path="/locations/{id}/users",
     *      tags={"Locations"},
     *      summary="Returns location users",
     *      description="Returns list of all users which belong to specific location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationUsersResponse")
     *      ),
     * )
     *
     * @param Location $location
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getUsers(Location $location)
    {
        $this->authorize('locations.view');

        return LocationUsersResponse::make($location->users);
    }

    /**
     * @OA\Post(
     *      path="/locations/{location_id}/users/{user_id}",
     *      tags={"Locations"},
     *      summary="Add user to specific location",
     *      description="Allows to make user a member of specific location",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AddUserToLocationRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either location or user doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. User is already a member of requested location.",
     *      ),
     * )
     *
     * @param Location                 $location
     * @param User                     $user
     * @param AddUserToLocationRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addUser(Location $location, User $user, AddUserToLocationRequest $request)
    {
        $this->authorize('locations.modify_members');

        $primary = $request->get('primary', false) ;
        $this->locationsService->addUser($location->id, $user->id, $primary);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/locations/{location_id}/users/{user_id}",
     *      tags={"Locations"},
     *      summary="Remove user from specific location",
     *      description="Allows to remove user from location members",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          description="User identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either location or user doesn't exist.",
     *      ),
     * )
     *
     * @param Location $location
     * @param User     $user
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeUser(Location $location, User $user)
    {
        $this->authorize('locations.modify_members');

        $this->locationsService->removeUser($location->id, $user->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/locations/{id}/suburbs",
     *      tags={"Locations"},
     *      summary="Returns location suburbs",
     *      description="Returns list of all suburbs which belong to specific location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationSuburbsResponse")
     *      ),
     * )
     *
     * @param Location $location
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSuburbs(Location $location)
    {
        $this->authorize('locations.view');

        return LocationSuburbsResponse::make($location->suburbs);
    }

    /**
     * @OA\Post(
     *      path="/locations/{location_id}/suburbs/{suburb_id}",
     *      tags={"Locations"},
     *      summary="Add suburb to specific location",
     *      description="Allows to add suburb to specific location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="suburb_id",
     *          in="path",
     *          required=true,
     *          description="Suburb identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either location or suburb doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Suburb has been already added earlier to this location.",
     *      ),
     * )
     *
     * @param Location $location
     * @param Suburb   $suburb
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addSuburb(Location $location, Suburb $suburb)
    {
        $this->authorize('locations.modify_suburbs');

        $this->locationsService->addSuburb($location->id, $suburb->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/locations/{location_id}/suburbs/{suburb_id}",
     *      tags={"Locations"},
     *      summary="Remove suburb from specific location",
     *      description="Allows remove suburb from specific location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *     @OA\Parameter(
     *          name="suburb_id",
     *          in="path",
     *          required=true,
     *          description="Suburb identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either location or suburb doesn't exist.",
     *      ),
     * )
     *
     * @param Location $location
     * @param Suburb   $suburb
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeSuburb(Location $location, Suburb $suburb)
    {
        $this->authorize('locations.modify_suburbs');

        $this->locationsService->removeSuburb($location->id, $suburb->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/locations/{location_id}/accounting-organization",
     *      tags={"Finance","Locations"},
     *      summary="Returns active accounting organization for given location",
     *      description="Returns active accounting organization for given location.
     **`finance.accounting_organizations.manage`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountingOrganizationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param int $locationId
     *
     * @return ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAccountingOrganizationByLocation(int $locationId): ApiOKResponse
    {
        $this->authorize('finance.accounting_organizations.manage');

        $accountingOrganization = $this->accountingOrganizationService
            ->findActiveAccountOrganizationByLocation($locationId);

        return AccountingOrganizationResponse::make($accountingOrganization);
    }
}
