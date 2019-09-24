<?php

namespace App\Http\Controllers\Meetings;

use App\Components\Meetings\Interfaces\MeetingsServiceInterface;
use App\Components\Meetings\Models\Meeting;
use App\Components\Meetings\Models\MeetingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Meetings\CreateMeetingRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Meetings\MeetingResponse;
use OpenApi\Annotations as OA;

/**
 * Class MeetingsController
 *
 * @package App\Http\Controllers\Meetings
 */
class MeetingsController extends Controller
{
    /**
     * @var MeetingsServiceInterface
     */
    private $meetingsService;

    /**
     * MeetingsController constructor.
     *
     * @param MeetingsServiceInterface $meetingsService
     */
    public function __construct(MeetingsServiceInterface $meetingsService)
    {
        $this->meetingsService = $meetingsService;
    }

    /**
     * @OA\Post(
     *      path="/meetings",
     *      tags={"Meetings"},
     *      summary="Create new meeting",
     *      description="Create new meeting",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateMeetingRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Meetings\CreateMeetingRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateMeetingRequest $request)
    {
        $this->authorize('meetings.create');
        $validatedWithUserId = array_merge($request->validated(), ['user_id' => $request->user()->id]);
        $meetingData         = new MeetingData($validatedWithUserId);

        $meeting = $this->meetingsService->addMeeting($meetingData);

        return MeetingResponse::make($meeting, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/meetings/{id}",
     *      tags={"Meetings"},
     *      summary="Get specific meeting info",
     *      description="Returns info about specific meeting",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Meetings\Models\Meeting $meeting
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Meeting $meeting)
    {
        $this->authorize('meetings.view');

        return MeetingResponse::make($this->meetingsService->getMeeting($meeting->id));
    }

    /**
     * @OA\Delete(
     *      path="/meetings/{id}",
     *      tags={"Meetings"},
     *      summary="Delete existing meeting",
     *      description="Delete existing meeting",
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
     *
     * @param \App\Components\Meetings\Models\Meeting $meeting
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Meeting $meeting)
    {
        $this->authorize('meetings.delete');
        $this->authorize('delete', $meeting);

        $this->meetingsService->deleteMeeting($meeting);

        return ApiOKResponse::make();
    }
}
