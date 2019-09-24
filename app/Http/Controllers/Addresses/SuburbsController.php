<?php

namespace App\Http\Controllers\Addresses;

use App\Components\Addresses\Interfaces\AddressServiceInterface;
use App\Components\Addresses\Models\Suburb;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\CreateSuburbRequest;
use App\Http\Requests\Addresses\GetSuburbsRequest;
use App\Http\Requests\Addresses\SearchSuburbsRequest;
use App\Http\Requests\Addresses\UpdateSuburbRequest;
use App\Http\Responses\Addresses\SuburbListResponse;
use App\Http\Responses\Addresses\SuburbResponse;
use App\Http\Responses\ApiOKResponse;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SuburbsController
 *
 * @package App\Http\Controllers\Addresses
 */
class SuburbsController extends Controller
{
    /** @var AddressServiceInterface */
    private $service;

    /**
     * SuburbsController constructor.
     *
     * @param AddressServiceInterface $service
     */
    public function __construct(AddressServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/suburbs",
     *      tags={"Addresses"},
     *      summary="Get list of suburbs",
     *      description="Returns list of suburbs",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="country_id",
     *          in="path",
     *          required=false,
     *          description="Allows to filter suburbs by country",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="state_id",
     *          in="path",
     *          required=false,
     *          description="Allows to filter suburbs by state",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SuburbListResponse"),
     *       ),
     *     )
     *
     * @param \App\Http\Requests\Addresses\GetSuburbsRequest $request
     *
     * @return \App\Http\Responses\Addresses\SuburbListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GetSuburbsRequest $request): SuburbListResponse
    {
        $this->authorize('suburbs.view');

        $query = Suburb::query();
        if ($request->has('country_id')) {
            $countryId = $request->input('country_id');
            $query->whereHas('state.country', function (Builder $query) use ($countryId) {
                return $query->where('id', $countryId);
            });
        }
        if ($request->has('state_id')) {
            $query->where('state_id', $request->input('state_id'));
        }

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $query->paginate(Paginator::resolvePerPage());

        return SuburbListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/suburbs",
     *      tags={"Addresses"},
     *      summary="Create new suburb",
     *      description="Create new suburb",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateSuburbRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SuburbResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Addresses\CreateSuburbRequest $request
     *
     * @return \App\Http\Responses\Addresses\SuburbResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateSuburbRequest $request): SuburbResponse
    {
        $this->authorize('suburbs.create');
        $suburb = Suburb::create($request->validated());

        return SuburbResponse::make($suburb, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/suburbs/{id}",
     *      tags={"Addresses"},
     *      summary="Allows to update specific suburb",
     *      description="Allows to update specific suburb",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateSuburbRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SuburbResponse")
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
     * @param \App\Http\Requests\Addresses\UpdateSuburbRequest $request
     * @param \App\Components\Addresses\Models\Suburb          $suburb
     *
     * @return \App\Http\Responses\Addresses\SuburbResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateSuburbRequest $request, Suburb $suburb): SuburbResponse
    {
        $this->authorize('suburbs.update');
        $suburb->fillFromRequest($request);

        return SuburbResponse::make($suburb->refresh());
    }


    /**
     * @OA\Get(
     *      path="/suburbs/{id}",
     *      tags={"Addresses"},
     *      summary="Returns full information about specific suburb",
     *      description="Returns full information about specific suburb",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SuburbResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Addresses\Models\Suburb $suburb
     *
     * @return \App\Http\Responses\Addresses\SuburbResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Suburb $suburb): SuburbResponse
    {
        $this->authorize('suburbs.view');

        return SuburbResponse::make($suburb);
    }

    /**
     * @OA\Delete(
     *      path="/suburbs/{id}",
     *      tags={"Addresses"},
     *      summary="Delete existing suburb",
     *      description="Delete existing suburb",
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
     * @param \App\Components\Addresses\Models\Suburb $suburb
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Suburb $suburb): ApiOKResponse
    {
        $this->authorize('suburbs.delete');
        $suburb->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/suburbs/search",
     *      tags={"Addresses"},
     *      summary="Get filtered set of suburbs",
     *      description="Allows to filter suburbs by name and state id.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Allows to search by full text",
     *         @OA\Schema(
     *            type="string",
     *            example="Williamstown",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="state_id",
     *         in="query",
     *         description="Allows to filter suburbs by state_id",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="count",
     *         in="query",
     *         description="Defines maximum number of items in result set",
     *         @OA\Schema(
     *            type="integer",
     *            example=10,
     *            default=15
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SuburbListResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Addresses\SearchSuburbsRequest $request
     *
     * @return \App\Http\Responses\Addresses\SuburbListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     */
    public function searchSuburbs(SearchSuburbsRequest $request): SuburbListResponse
    {
        $this->authorize('suburbs.view');
        $suburbs = $this->service->searchSuburbs($request->validated());

        return SuburbListResponse::make($suburbs);
    }
}
