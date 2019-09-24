<?php

namespace App\Http\Controllers\Management;

use App\Exceptions\Api\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Management\SeederRequest;
use App\Http\Responses\ApiOKResponse;
use App\Jobs\Management\SeederJob;

/**
 * Class SeederController
 *
 * @package App\Http\Controllers\Management
 */
class SeederController extends Controller
{
    /**
     * @OA\Post(
     *      path="/management/seed",
     *      tags={"Management"},
     *      summary="Allows to run existing seeder",
     *      description="Allows to run existing seeder",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SeederRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *       @OA\Response(
     *          response=404,
     *          description="Requested seeder does not exists",
     *      ),
     * )
     * @param \App\Http\Requests\Management\IndexModelRequests $requests
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function seed(SeederRequest $request)
    {
        $this->authorize('management.seed');

        $class = $request->class;
        if (false === class_exists($class)) {
            throw new NotFoundException('Requested seeder does not exists');
        }

        SeederJob::dispatch($request->class);

        return ApiOKResponse::make();
    }
}
