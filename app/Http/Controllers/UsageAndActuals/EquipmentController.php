<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Jobs\Models\Job;
use App\Components\Notes\Models\Note;
use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Exceptions\NotAllowedException;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateEquipmentRequest;
use App\Http\Requests\UsageAndActuals\SearchEquipmentRequest;
use App\Http\Requests\UsageAndActuals\UpdateEquipmentRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notes\NoteListResponse;
use App\Http\Responses\UsageAndActuals\EquipmentListResponse;
use App\Http\Responses\UsageAndActuals\EquipmentResponse;
use App\Http\Responses\UsageAndActuals\EquipmentSearchResponse;
use Exception;

/**
 * Class EquipmentController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class EquipmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Get list of equipment",
     *     description="Returns list of equipment. **`equipment.view`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/EquipmentListResponse"),
     *     ),
     * )
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): EquipmentListResponse
    {
        $this->authorize('equipment.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Equipment::paginate(Paginator::resolvePerPage());

        return EquipmentListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/usage-and-actuals/equipment",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Create new equipment",
     *     description="Create new equipment **`management.equipment`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateEquipmentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *        response=201,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateEquipmentRequest $request
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateEquipmentRequest $request): EquipmentResponse
    {
        $this->authorize('management.equipment');

        $equipment = Equipment::create($request->validated());

        return EquipmentResponse::make($equipment, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Returns full information about equipment",
     *     description="Returns full information about equipment **`equipment.view`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\Equipment $equipment
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Equipment $equipment): EquipmentResponse
    {
        $this->authorize('equipment.view');

        return EquipmentResponse::make($equipment);
    }

    /**
     * @OA\Patch(
     *     path="/usage-and-actuals/equipment/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Update existing equipment",
     *     description="Update existing equipment **`management.equipment`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateEquipmentRequest")
     *         )
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/EquipmentResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\UpdateEquipmentRequest $request
     * @param \App\Components\UsageAndActuals\Models\Equipment          $equipment
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResponse
    {
        $this->authorize('management.equipment');

        $equipment->fillFromRequest($request);

        return EquipmentResponse::make($equipment);
    }

    /**
     * @OA\Delete(
     *     path="/usage-and-actuals/equipment/{id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Delete existing equipment",
     *     description="Delete existing equipment **`management.equipment`** permission is required
    to perform this operation.",
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
     * @param \App\Components\UsageAndActuals\Models\Equipment $equipment
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Equipment $equipment): ApiOKResponse
    {
        $this->authorize('management.equipment');
        $equipment->delete();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment/{id}/notes",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Returns list of notes attached to specified equipment",
     *     description="**equipment.view ** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *        @OA\JsonContent(ref="#/components/schemas/NoteListResponse")
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Unauthorized",
     *        @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *        response=404,
     *        description="Not found. Requested resource couldn't be found.",
     *     ),
     * )
     * @param Equipment $equipment
     *
     * @return \App\Http\Responses\Notes\NoteListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getNotes(Equipment $equipment): NoteListResponse
    {
        $this->authorize('equipment.view');

        $result = $equipment->notes()->with('documents', 'user', 'user.avatar')->get();

        return NoteListResponse::make($result);
    }

    /**
     * @OA\Post(
     *     path="/usage-and-actuals/equipment/{id}/notes/{note_id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Allows to attach a note to an equipment",
     *     description="**equipment.notes.edit** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="equipment_id",
     *         in="path",
     *         required=true,
     *         description="Equipment identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="note_id",
     *         in="path",
     *         required=true,
     *         description="Note identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Unauthorized",
     *        @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *        response=404,
     *        description="Not found. Either equipment or note doesn't exist.",
     *     ),
     *     @OA\Response(
     *        response=405,
     *        description="Not allowed. Note is already attached to this equipment.",
     *     ),
     * )
     *
     * @param Equipment $equipment
     * @param Note      $note
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     * @throws \App\Components\UsageAndActuals\Exceptions\NotAllowedException
     */
    public function attachNote(Equipment $equipment, Note $note): ApiOKResponse
    {
        $this->authorize('equipment.notes.edit');
        $this->authorize('attach', $note);

        try {
            $equipment->notes()->attach($note);
        } catch (Exception $e) {
            throw new NotAllowedException('This note is already attached to the equipment.');
        }

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *     path="/usage-and-actuals/equipment/{id}/notes/{note_id}",
     *     tags={"Usage and Actuals", "Equipment"},
     *     summary="Allows to detach a note from an equipment",
     *     description="**equipment.notes.edit** permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="equipment_id",
     *         in="path",
     *         required=true,
     *         description="Equipment identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="note_id",
     *         in="path",
     *         required=true,
     *         description="Note identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *        response=200,
     *        description="OK",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Unauthorized",
     *        @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *        response=404,
     *        description="Not found. Either equipment or note doesn't exist.",
     *     ),
     * )
     *
     * @param Equipment $equipment
     * @param Note      $note
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachNote(Equipment $equipment, Note $note): ApiOKResponse
    {
        $this->authorize('equipment.notes.edit');
        $this->authorize('detach', $note);

        $equipment->notes()->detach($note);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *     path="/usage-and-actuals/equipment/search",
     *     summary="Allows to search equipment for barcode, make, model, serial number or category name",
     *     description="**equipment.view** permission is required to perform this operation.",
     *     tags={"Usage and Actuals", "Equipment", "Search"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="term",
     *         in="query",
     *         required=true,
     *         description="Search term",
     *         @OA\Schema(
     *             type="string",
     *             example="absorber",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="job_id",
     *         in="query",
     *         required=false,
     *         description="If provided then insurer contract from job will be included to equipment category and
    equipment will be filtered by job assigned location id.",
     *         @OA\Schema(
     *             type="int",
     *             example=1,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Matching equipment",
     *         @OA\JsonContent(ref="#/components/schemas/EquipmentSearchResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param SearchEquipmentRequest $request
     *
     * @return \App\Http\Responses\UsageAndActuals\EquipmentSearchResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchEquipmentRequest $request): EquipmentSearchResponse
    {
        $this->authorize('equipment.view');

        if (null !== $request->getJobId()) {
            $job = Job::findOrFail($request->getJobId());
        }

        $response = Equipment::searchOnName(
            $request->getOptions(),
            isset($job) ? $job->assigned_location_id : null,
            isset($job) ? $job->insurer_contract_id : null
        );

        return EquipmentSearchResponse::make($response);
    }
}
