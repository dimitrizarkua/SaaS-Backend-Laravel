<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Messages\Models\Message;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\AttachMessageToJobRequest;
use App\Http\Requests\Jobs\ComposeMessageRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobMessagesListResponse;
use App\Http\Responses\Jobs\JobMessageTextResponse;

/**
 * Class JobMessagesController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobMessagesController extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobUsersServiceInterface
     */
    protected $service;

    /**
     * JobMessagesController constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobMessagesServiceInterface $service
     */
    public function __construct(JobMessagesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/jobs/{id}/messages",
     *      tags={"Jobs"},
     *      summary="Returns list of messages attached to a Job.",
     *      description="Returns list of incoming and outgoing messages attached a Job
    sorted in reverse chronological order.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMessagesListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function listMessages(Job $job)
    {
        $this->authorize('jobs.view');

        $result = $job->messages()->with('sender', 'recipients', 'documents', 'latestStatus')->get();

        return JobMessagesListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/messages/{message_id}",
     *      tags={"Jobs"},
     *      summary="Attach a message to specific job",
     *      description="Allows to attach a message to a job. Only draft messages can be attached.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AttachMessageToJobRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          required=true,
     *          description="Message identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or message doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job is closed, message is already attached to this job or can't be sent.",
     *      ),
     * )
     *
     * @param Job                       $job
     * @param Message                   $message
     * @param AttachMessageToJobRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function attachMessage(Job $job, Message $message, AttachMessageToJobRequest $request)
    {
        $this->authorize('jobs.manage_messages');
        $this->authorize('attach', $message);

        $sendImmediately = $request->post('send_immediately', true);
        if ($sendImmediately) {
            $this->authorize('send', $message);
        }

        if (!$message->isDraft() || $message->is_incoming) {
            throw new NotAllowedException('Only draft outgoing messages can be attached to a job.');
        }

        $this->service->attachMessage($job->id, $message->id, $sendImmediately);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{job_id}/messages/{message_id}/send",
     *      tags={"Jobs"},
     *      summary="Send message previously attached to specific job",
     *      description="Allows to send a message which was previously attached to a job.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          required=true,
     *          description="Message identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or message doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Job is closed, message is not attached to a job or can't be sent.",
     *      ),
     * )
     *
     * @param Job     $job
     * @param Message $message
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function sendMessage(Job $job, Message $message)
    {
        $this->authorize('jobs.manage_messages');
        $this->authorize('send', $message);

        $this->service->sendMessage($job->id, $message->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/jobs/{job_id}/messages/{message_id}",
     *      tags={"Jobs"},
     *      summary="Detach a message from specific job",
     *      description="Allows to detach a message from a job. Only draft messages can be attached.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="job_id",
     *          in="path",
     *          required=true,
     *          description="Job identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          required=true,
     *          description="Message identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or message doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Incoming and sent messages can't be detached, or job is closed.",
     *      ),
     * )
     *
     * @param Job     $job
     * @param Message $message
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function detachMessage(Job $job, Message $message)
    {
        $this->authorize('jobs.manage_messages');
        $this->authorize('detach', $message);

        if (!$message->isDraft() || $message->is_incoming) {
            throw new NotAllowedException('Sent and incoming messages can\'t be detached.');
        }

        $this->service->detachMessage($job->id, $message->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/jobs/{id}/messages/from-template",
     *      tags={"Jobs"},
     *      summary="Compose message using a specified template",
     *      description="Allows to compose a job message from a template",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ComposeMessageRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobMessageTextResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested job or template couldn't be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Jobs\ComposeMessageRequest $request
     * @param int                                           $jobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function composeMessage(ComposeMessageRequest $request, int $jobId)
    {
        $this->authorize('jobs.manage_messages');
        $composedMessage = $this->service->composeMessage($jobId, $request->getTemplateId());

        return JobMessageTextResponse::make($composedMessage);
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{id}/messages/read",
     *      tags={"Jobs"},
     *      summary="Mark all unread job messages as read",
     *      description="Allows to mark all unread job messages as read",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested job couldn't be found.",
     *      ),
     * )
     *
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function markMessagesAsRead(int $jobId)
    {
        $this->authorize('jobs.manage_messages');
        $this->service->readAllIncomingMessages($jobId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Patch(
     *      path="/jobs/{id}/messages/unread",
     *      tags={"Jobs"},
     *      summary="Mark the latest job message as unread",
     *      description="Allows to mark the latest job message as unread",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested job couldn't be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. The job is closed or has no messages.",
     *      ),
     * )
     *
     * @param int $jobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function markLatestMessageAsUnread(int $jobId)
    {
        $this->authorize('jobs.manage_messages');
        $this->service->unreadLatestIncomingMessage($jobId);

        return ApiOKResponse::make();
    }
}
