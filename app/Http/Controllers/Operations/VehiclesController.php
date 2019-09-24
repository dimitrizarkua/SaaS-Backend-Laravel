<?php

namespace App\Http\Controllers\Operations;

use App\Components\Operations\Interfaces\VehiclesServiceInterface;
use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VO\VehicleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\ChangeVehicleStatusRequest;
use App\Http\Requests\Operations\CreateVehicleRequest;
use App\Http\Requests\Operations\ListVehiclesRequest;
use App\Http\Requests\Operations\UpdateVehicleRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Operations\FullVehicleResponse;
use App\Http\Responses\Operations\VehicleListResponse;
use App\Http\Responses\Operations\VehicleResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class VehiclesController
 *
 * @package App\Http\Controllers\Operations
 */
class VehiclesController extends Controller
{
    /** @var \App\Components\Operations\Interfaces\VehiclesServiceInterface $service */
    private $service;

    /**
     * VehiclesController constructor.
     *
     * @param \App\Components\Operations\Interfaces\VehiclesServiceInterface $service
     */
    public function __construct(VehiclesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/operations/vehicles",
     *      tags={"Operations"},
     *      summary="Returns list of all location's vehicles",
     *      description="Allows to retrieve vehicles assigned to the specified location",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="location_id",
     *          in="query",
     *          required=true,
     *          description="Location identifier",
     *          @OA\Schema(
     *             type="integer",
     *             example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          required=true,
     *          description="Requested date",
     *          @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2018-11-10"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested location could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\ListVehiclesRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listLocationVehicles(ListVehiclesRequest $request)
    {
        $this->authorize('operations.vehicles.view');
        $vehicles = $this->service->listLocationVehicles($request->getLocationId(), $request->getDate());

        return VehicleListResponse::make($vehicles);
    }

    /**
     * @OA\Post(
     *      path="/operations/vehicles",
     *      tags={"Operations"},
     *      summary="Create new vehicle",
     *      description="Allows to create new vehicle",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateVehicleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateVehicleRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateVehicleRequest $request)
    {
        $this->authorize('operations.vehicles.manage');

        $data = new VehicleData($request->validated());
        $vehicle = $this->service->createVehicle($data, Auth::id());

        return VehicleResponse::make($vehicle, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/operations/vehicles/{id}",
     *      tags={"Operations"},
     *      summary="Retrieve information about specific vehicle",
     *      description="Allows to retrieve information about specific vehicle",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullVehicleResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Operations\Models\Vehicle $vehicle
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('operations.vehicles.view');

        return FullVehicleResponse::make($vehicle);
    }

    /**
     * @OA\Patch(
     *      path="/operations/vehicles/{id}",
     *      tags={"Operations"},
     *      summary="Update existing vehicle",
     *      description="Allows to update existing vehicle",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateVehicleRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\UpdateVehicleRequest $request
     * @param \App\Components\Operations\Models\Vehicle          $vehicle
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $this->authorize('operations.vehicles.manage');

        $vehicle->fillFromRequest($request);

        return VehicleResponse::make($vehicle);
    }

    /**
     * @OA\Patch(
     *      path="/operations/vehicles/{id}/status",
     *      tags={"Operations"},
     *      summary="Change status of the vehicle",
     *      description="Allows to change status of the vehicle",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ChangeVehicleStatusRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\ChangeVehicleStatusRequest $request
     * @param int                                                      $vehicleId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeStatus(ChangeVehicleStatusRequest $request, int $vehicleId)
    {
        $this->authorize('operations.vehicles.change_status');

        $status = $this->service->changeVehicleStatus($vehicleId, $request->getStatusTypeId(), Auth::id());

        return VehicleResponse::make($status->vehicle);
    }

    /**
     * @OA\Delete(
     *      path="/operations/vehicles/{id}",
     *      tags={"Operations"},
     *      summary="Delete existing vehicle",
     *      description="Allow to delete existing vehicle",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not be deleted since another entity refers to it.",
     *      ),
     * )
     * @param int $vehicleId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $vehicleId)
    {
        $this->authorize('operations.vehicles.manage');

        $this->service->deleteVehicle($vehicleId);

        return ApiOKResponse::make();
    }
}
