<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\VO\EquipmentCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateEquipmentCategoryRequest;
use App\Http\Requests\UsageAndActuals\UpdateEquipmentCategoryRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\EquipmentCategoryListResponse;
use App\Http\Responses\UsageAndActuals\EquipmentCategoryResponse;

/**
 * Class EquipmentCategoriesController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class EquipmentCategoriesController extends Controller
{
    /**
     * @var \App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface
     */
    private $service;

    /**
     * EquipmentCategoriesController constructor.
     *
     * @param \App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface $service
     */
    public function __construct(EquipmentCategoriesInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment-categories",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Get list of equipment categories",
     *     description="Returns list of equipment categories. **`equipment.view`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/EquipmentCategoryListResponse"),
     *     ),
     * )
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentCategoryListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('equipment.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = EquipmentCategory::paginate(Paginator::resolvePerPage());

        return EquipmentCategoryListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/usage-and-actuals/equipment-categories",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Create new equipment category",
     *     description="Create new equipment category and charging intervals. **`management.equipment`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateEquipmentCategoryRequest")
     *         )
     *     ),
     *     @OA\Response(
     *        response=201,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentCategoryResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateEquipmentCategoryRequest $request
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentCategoryResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function store(CreateEquipmentCategoryRequest $request)
    {
        $this->authorize('management.equipment');

        $data              = new EquipmentCategoryData($request->validated());
        $equipmentCategory = $this->service->createEquipmentCategory($data);

        return EquipmentCategoryResponse::make($equipmentCategory, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment-categories/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Returns full information about equipment category",
     *     description="Returns full information about equipment category **`equipment.view`**
    permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentCategoryResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\EquipmentCategory $equipmentCategory
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentCategoryResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(EquipmentCategory $equipmentCategory)
    {
        $this->authorize('equipment.view');

        return EquipmentCategoryResponse::make($equipmentCategory);
    }

    /**
     * @OA\Patch(
     *     path="/usage-and-actuals/equipment-categories/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Update existing equipment catery",
     *     description="Update existing equipment caterogy **`management.equipment`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateEquipmentCategoryRequest")
     *         )
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentCategoryResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\UpdateEquipmentCategoryRequest $request
     * @param \App\Components\UsageAndActuals\Models\EquipmentCategory          $equipmentCategory
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentCategoryResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateEquipmentCategoryRequest $request, EquipmentCategory $equipmentCategory)
    {
        $this->authorize('management.equipment');

        $equipmentCategory->fillFromRequest($request);

        return EquipmentCategoryResponse::make($equipmentCategory);
    }

    /**
     * @OA\Delete(
     *     path="/usage-and-actuals/equipment-categories/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Delete existing equipment category",
     *     description="Delete existing equipment category **`management.equipment`**
    permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\EquipmentCategory $equipmentCategory
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(EquipmentCategory $equipmentCategory)
    {
        $this->authorize('management.equipment');
        $equipmentCategory->delete();

        return ApiOKResponse::make();
    }
}
