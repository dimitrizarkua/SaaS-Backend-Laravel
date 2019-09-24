<?php

namespace App\Http\Controllers\Operations;

use App\Components\Operations\Exceptions\NotAllowedException;
use App\Components\Operations\Models\VehicleStatusType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CreateVehicleStatusTypeRequest;
use App\Http\Requests\Operations\UpdateVehicleStatusTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Operations\VehicleStatusTypeListResponse;
use App\Http\Responses\Operations\VehicleStatusTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class VehicleStatusTypesController
 *
 * @package App\Http\Controllers\Operations
 */
class VehicleStatusTypesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/operations/vehicles/statuses/types",
     *      tags={"Operations"},
     *      summary="Returns list of all vehicle status types",
     *      description="Allows to get a paginated list of all the vehicle status types.
                        `management.system.settings` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleStatusTypeListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('management.system.settings');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = VehicleStatusType::paginate(Paginator::resolvePerPage());

        return VehicleStatusTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/operations/vehicles/statuses/types",
     *      tags={"Operations"},
     *      summary="Create new vehicle status type",
     *      description="Allows to create new vehicle status type.
                        `management.system.settings` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateVehicleStatusTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleStatusTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Operations\CreateVehicleStatusTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateVehicleStatusTypeRequest $request)
    {
        $this->authorize('management.system.settings');

        $vehicleStatusType = VehicleStatusType::create($request->validated());
        $vehicleStatusType->saveOrFail();

        return VehicleStatusTypeResponse::make($vehicleStatusType, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/operations/vehicles/statuses/types/{id}",
     *      tags={"Operations"},
     *      summary="Retrieve information about specific vehicle status type",
     *      description="Allows to retrieve information about specific vehicle status type.
                        `management.system.settings` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleStatusTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Operations\Models\VehicleStatusType $vehicleStatusType
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(VehicleStatusType $vehicleStatusType)
    {
        $this->authorize('management.system.settings');

        return VehicleStatusTypeResponse::make($vehicleStatusType);
    }

    /**
     * @OA\Patch(
     *      path="/operations/vehicles/statuses/types/{id}",
     *      tags={"Operations"},
     *      summary="Update existing vehicle status type",
     *      description="Allows to update existing vehicle status type.
                        `management.system.settings` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateVehicleStatusTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleStatusTypeResponse")
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
     * @param \App\Http\Requests\Operations\UpdateVehicleStatusTypeRequest $request
     * @param \App\Components\Operations\Models\VehicleStatusType          $vehicleStatusType
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateVehicleStatusTypeRequest $request, VehicleStatusType $vehicleStatusType)
    {
        $this->authorize('management.system.settings');

        $vehicleStatusType->fillFromRequest($request);

        return VehicleStatusTypeResponse::make($vehicleStatusType);
    }

    /**
     * @OA\Delete(
     *      path="/operations/vehicles/statuses/types/{id}",
     *      tags={"Operations"},
     *      summary="Delete existing vehicle status type",
     *      description="Allows to delete existing vehicle status type.
                        `management.system.settings` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
      *         response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Requested vehicle status type is default or is in use.",
     *      ),
     * )
     * @param \App\Components\Operations\Models\VehicleStatusType $vehicleStatusType
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(VehicleStatusType $vehicleStatusType)
    {
        $this->authorize('management.system.settings');

        if ($vehicleStatusType->is_default) {
            throw new NotAllowedException('Could not delete default vehicle status');
        }
        try {
            $vehicleStatusType->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }

        return ApiOKResponse::make();
    }
}
