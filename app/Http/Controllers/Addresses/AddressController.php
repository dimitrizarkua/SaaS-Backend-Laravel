<?php

namespace App\Http\Controllers\Addresses;

use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Addresses\Models\Address;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\CreateAddressRequest;
use App\Http\Requests\Addresses\GetAddressesRequest;
use App\Http\Requests\Addresses\ParseAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Responses\Addresses\AddressListResponse;
use App\Http\Responses\Addresses\AddressResponse;
use App\Http\Responses\Addresses\FullAddressResponse;
use App\Http\Responses\ApiOKResponse;

/**
 * Class AddressController
 *
 * @package App\Http\Controllers\Addresses
 */
class AddressController extends Controller
{
    /**
     * @var \App\Components\Addresses\Interfaces\AddressServiceInterface
     */
    private $addressService;

    /**
     * AddressController constructor.
     *
     * @param AddressServiceInterface $addressService
     */
    public function __construct(AddressServiceInterface $addressService)
    {
        $this->addressService = $addressService;
    }

    /**
     * @OA\Get(
     *      path="/addresses",
     *      tags={"Addresses"},
     *      summary="Get list of addresses",
     *      description="Returns list of addresses",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="state_id",
     *          in="path",
     *          required=false,
     *          description="Allows to filter by state_id",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="suburb_id",
     *          in="path",
     *          required=false,
     *          description="Allows to filter by suburb_id",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address_line",
     *          in="path",
     *          required=false,
     *          description="Allows to filter by address line",
     *          @OA\Schema(
     *              type="string",
     *              example="Ocean avenue",
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AddressListResponse"),
     *       ),
     *     )
     *
     * @param \App\Http\Requests\Addresses\GetAddressesRequest $request
     *
     * @return \App\Http\Responses\Addresses\AddressListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GetAddressesRequest $request): AddressListResponse
    {
        $this->authorize('addresses.view');

        $query = Address::search($request->validated());
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $query->paginate(Paginator::resolvePerPage());

        return AddressListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/addresses",
     *      tags={"Addresses"},
     *      summary="Create new address",
     *      description="Create new address",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateAddressRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AddressResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Addresses\CreateAddressRequest $request
     *
     * @return \App\Http\Responses\Addresses\AddressResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateAddressRequest $request): AddressResponse
    {
        $this->authorize('addresses.create');
        $state = Address::create($request->validated());

        return AddressResponse::make($state, null, 201);
    }

    /**
     * @OA\Post(
     *      path="/addresses/parse",
     *      tags={"Addresses"},
     *      summary="Parse address string and create new address-entity",
     *      description="Parse address string and create new address-entity",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ParseAddressRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullAddressResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Addresses\ParseAddressRequest $request
     *
     * @return \App\Http\Responses\Addresses\FullAddressResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function parseAddress(ParseAddressRequest $request): FullAddressResponse
    {
        $this->authorize('addresses.create');
        $address = $this->addressService->parseAddress($request->getAddress());

        return FullAddressResponse::make($address);
    }

    /**
     * @OA\Get(
     *      path="/addresses/{id}",
     *      tags={"Addresses"},
     *      summary="Returns full information about specific address",
     *      description="Returns full information about specific address",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullAddressResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $addressId
     *
     * @return \App\Http\Responses\Addresses\FullAddressResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $addressId): FullAddressResponse
    {
        $this->authorize('addresses.view');
        $address = Address::with(['suburb', 'suburb.state', 'suburb.state.country'])
            ->findOrFail($addressId);

        return FullAddressResponse::make($address);
    }

    /**
     * @OA\Patch(
     *      path="/addresses/{id}",
     *      tags={"Addresses"},
     *      summary="Allows to update specific address",
     *      description="Allows to update specific address",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateAddressRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullAddressResponse")
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
     * @param \App\Http\Requests\Addresses\UpdateAddressRequest $request
     * @param int                                               $addressId
     *
     * @return \App\Http\Responses\Addresses\FullAddressResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateAddressRequest $request, int $addressId): FullAddressResponse
    {
        $this->authorize('addresses.update');
        $address = Address::with(['suburb', 'suburb.state', 'suburb.state.country'])
            ->findOrFail($addressId);
        $address->fillFromRequest($request);

        return FullAddressResponse::make($address->refresh());
    }

    /**
     * @OA\Delete(
     *      path="/addresses/{id}",
     *      tags={"Addresses"},
     *      summary="Delete existing address",
     *      description="Delete existing address",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Addresses\Models\Address $address
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Address $address): ApiOKResponse
    {
        $this->authorize('addresses.delete');
        $address->delete();

        return ApiOKResponse::make();
    }
}
