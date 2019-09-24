<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Models\Material;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateMaterialRequest;
use App\Http\Requests\UsageAndActuals\SearchMaterialsRequest;
use App\Http\Requests\UsageAndActuals\UpdateMaterialRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\MaterialListResponse;
use App\Http\Responses\UsageAndActuals\MaterialResponse;
use App\Http\Responses\UsageAndActuals\MaterialSearchResponse;
use Illuminate\Support\Collection;

/**
 * Class MaterialsController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class MaterialsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/usage-and-actuals/materials",
     *      tags={"Usage and Actuals", "Materials"},
     *      summary="Get list of materials",
     *      description="Returns list of materials. **`jobs.usage.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MaterialListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('jobs.usage.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Material::paginate(Paginator::resolvePerPage());

        return MaterialListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/materials",
     *      tags={"Usage and Actuals", "Materials"},
     *      summary="Create new material.",
     *      description="Create new material. **`management.materials`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateMaterialRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MaterialResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateMaterialRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateMaterialRequest $request)
    {
        $this->authorize('management.materials');

        $material = Material::create($request->validated());
        $material->saveOrFail();

        return MaterialResponse::make($material, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/materials/{id}",
     *      tags={"Usage and Actuals", "Materials"},
     *      summary="Returns full information about material.",
     *      description="Returns full information about material. **`jobs.usage.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MaterialResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\Material $material
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Material $material)
    {
        $this->authorize('jobs.usage.view');

        return MaterialResponse::make($material);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/materials/{id}",
     *      tags={"Usage and Actuals", "Materials"},
     *      summary="Update existing material.",
     *      description="Update existing material. **`management.materials`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateMaterialRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MaterialResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\UpdateMaterialRequest $request
     * @param \App\Components\UsageAndActuals\Models\Material          $material
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $this->authorize('management.materials');

        $material->fillFromRequest($request);

        return MaterialResponse::make($material);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/materials/{id}",
     *      tags={"Usage and Actuals", "Materials"},
     *      summary="Delete existing material.",
     *      description="Delete existing material. **`management.materials`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\Material $material
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Material $material)
    {
        $this->authorize('management.materials');

        $material->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/materials/search",
     *     tags={"Usage and Actuals", "Materials", "Search"},
     *     summary="Get filtered set of materials",
     *     description="Allows to search materials by name",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Allows to search materials by name",
     *         @OA\Schema(
     *             type="string",
     *             example="water",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MaterialSearchResponse")
     *     ),
     * )
     * @param \App\Http\Requests\UsageAndActuals\SearchMaterialsRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchMaterialsRequest $request)
    {
        $this->authorize('jobs.usage.view');

        $materials = Material::search($request->getName())
            ->raw();

        $response = Collection::make(mapElasticResults($materials));

        return MaterialSearchResponse::make($response);
    }
}
