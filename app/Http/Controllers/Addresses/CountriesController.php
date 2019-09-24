<?php

namespace App\Http\Controllers\Addresses;

use App\Components\Addresses\Helpers\CountryHelper;
use App\Components\Addresses\Models\Country;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\CreateCountryRequest;
use App\Http\Responses\Addresses\CountryListResponse;
use App\Http\Responses\Addresses\CountryResponse;
use App\Http\Responses\ApiOKResponse;

/**
 * Class CountriesController
 *
 * @package App\Http\Controllers\Addresses
 */
class CountriesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/countries",
     *      tags={"Addresses"},
     *      summary="Get list of countries",
     *      description="Returns list of countries",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CountryListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('countries.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Country::paginate(Paginator::resolvePerPage());

        return new CountryListResponse($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/countries",
     *      tags={"Addresses"},
     *      summary="Create new country",
     *      description="Create new country",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCountryRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CountryResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateCountryRequest $request)
    {
        $this->authorize('countries.create');
        $countryName = $request->getCountryName();
        $country     = Country::create([
            'name'            => $countryName,
            'iso_alpha2_code' => CountryHelper::getAlpha2Code($countryName),
            'iso_alpha3_code' => CountryHelper::getAlpha3Code($countryName),
        ]);

        return CountryResponse::make($country, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/countries/{id}",
     *      tags={"Addresses"},
     *      summary="Returns full information about specific country",
     *      description="Returns full information about specific country",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CountryResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Country $country)
    {
        $this->authorize('countries.view');
        return CountryResponse::make($country);
    }

    /**
     * @OA\Delete(
     *      path="/countries/{id}",
     *      tags={"Addresses"},
     *      summary="Delete existing country",
     *      description="Delete existing country",
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(Country $country)
    {
        $this->authorize('countries.delete');
        $country->delete();

        return ApiOKResponse::make();
    }
}
