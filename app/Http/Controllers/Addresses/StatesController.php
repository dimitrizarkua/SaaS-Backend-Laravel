<?php

namespace App\Http\Controllers\Addresses;

use App\Components\Addresses\Models\State;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\CreateStateRequest;
use App\Http\Requests\Addresses\GetStatesRequest;
use App\Http\Requests\Addresses\UpdateStateRequest;
use App\Http\Responses\Addresses\StateListResponse;
use App\Http\Responses\Addresses\StateResponse;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class StatesController
 *
 * @package App\Http\Controllers\Addresses
 */
class StatesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/states",
     *      tags={"Addresses"},
     *      summary="Get list of states",
     *      description="Returns list of states",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="country_id",
     *          in="path",
     *          required=false,
     *          description="Allows to filter states by country_id",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StateListResponse"),
     *       ),
     *     )
     *
     * @param \App\Http\Requests\Addresses\GetStatesRequest $request
     *
     * @return \App\Http\Responses\Addresses\StateListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GetStatesRequest $request): StateListResponse
    {
        $this->authorize('states.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $query = State::query();
        if ($request->has('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }
        $pagination = $query->paginate(Paginator::resolvePerPage());

        return StateListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/states",
     *      tags={"Addresses"},
     *      summary="Create new state",
     *      description="Create new state",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateStateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StateResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Addresses\CreateStateRequest $request
     *
     * @return \App\Http\Responses\Addresses\StateResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateStateRequest $request): StateResponse
    {
        $this->authorize('states.create');
        $state = State::create($request->validated());

        return StateResponse::make($state, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/states/{id}",
     *      tags={"Addresses"},
     *      summary="Returns full information about specific state",
     *      description="Returns full information about specific state",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StateResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Addresses\Models\State $state
     *
     * @return \App\Http\Responses\Addresses\StateResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(State $state): StateResponse
    {
        $this->authorize('states.view');

        return StateResponse::make($state);
    }

    /**
     * @OA\Patch(
     *      path="/states/{id}",
     *      tags={"Addresses"},
     *      summary="Allows to update specific state",
     *      description="Allows to update specific state",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateStateRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/StateResponse")
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
     * @param \App\Http\Requests\Addresses\UpdateStateRequest $request
     * @param \App\Components\Addresses\Models\State          $state
     *
     * @return \App\Http\Responses\Addresses\StateResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateStateRequest $request, State $state): StateResponse
    {
        $this->authorize('states.update');
        $state->fillFromRequest($request);

        return StateResponse::make($state->refresh());
    }

    /**
     * @OA\Delete(
     *      path="/states/{id}",
     *      tags={"Addresses"},
     *      summary="Delete existing state",
     *      description="Delete existing state",
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
     * @param \App\Components\Addresses\Models\State $state
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(State $state): ApiOKResponse
    {
        $this->authorize('states.delete');
        $state->delete();

        return ApiOKResponse::make();
    }
}
