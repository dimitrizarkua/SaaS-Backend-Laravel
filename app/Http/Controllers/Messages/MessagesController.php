<?php

namespace App\Http\Controllers\Messages;

use App\Components\Documents\Models\Document;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\Message;
use App\Http\Controllers\Controller;
use App\Http\Requests\Messages\CreateMessageRequest;
use App\Http\Requests\Messages\UpdateMessageRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Messages\FullMessageResponse;
use OpenApi\Annotations as OA;

/**
 * Class MessagesController
 *
 * @package App\Http\Controllers\Messages
 */
class MessagesController extends Controller
{
    /**
     * @var MessagingServiceInterface
     */
    private $service;

    /**
     * MessagesController constructor.
     *
     * @param \App\Components\Messages\Interfaces\MessagingServiceInterface $service
     */
    public function __construct(MessagingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/messages",
     *      tags={"Messages"},
     *      summary="Create new message",
     *      description="Allows to create new message",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateMessageRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullMessageResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param CreateMessageRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateMessageRequest $request)
    {
        $this->authorize('messages.manage');

        $user = $request->user();

        $messageData = $request->toMessageData();
        $messageData->setSenderId($user->id);

        $message = $this->service->createOutgoingMessage($messageData);

        return FullMessageResponse::make($message, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/messages/{id}",
     *      tags={"Messages"},
     *      summary="Get full information about specific message",
     *      description="Retrieve full information about specific message",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullMessageResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param Message $message
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Message $message)
    {
        $this->authorize('messages.view');

        return FullMessageResponse::make($message);
    }

    /**
     * @OA\Patch(
     *      path="/messages/{id}",
     *      tags={"Messages"},
     *      summary="Update existing draft message",
     *      description="Allows to update existing draft message.
    Message should be owned by currently authenticated user. **Important!** Please send all the fields every time you
    call this endpoint, do not send only updated fields!",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateMessageRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullMessageResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own message.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not Allowed. Requested message could not be edited.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param UpdateMessageRequest $request
     * @param Message              $message
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateMessageRequest $request, Message $message)
    {
        $this->authorize('messages.manage');
        $this->authorize('update', $message);

        $user = $request->user();

        $messageData = $request->toMessageData();
        $messageData->setSenderId($user->id);

        $this->service->updateOutgoingMessage($message->id, $messageData);

        return FullMessageResponse::make($message);
    }

    /**
     * @OA\Delete(
     *      path="/messages/{id}",
     *      tags={"Messages"},
     *      summary="Delete existing message",
     *      description="Allows to delete existing draft message.
    Message should be owned by currently authenticated user.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not Allowed. Requested message could not be deleted.",
     *      ),
     * )
     * @param Message $message
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Message $message)
    {
        $this->authorize('messages.manage');
        $this->authorize('delete', $message);

        $this->service->deleteMessage($message->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/messages/{message_id}/documents/{document_id}",
     *      tags={"Messages","Documents"},
     *      summary="Attach document to specific message",
     *      description="Allows to attach a document to specific message",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          required=true,
     *          description="Message identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="document_id",
     *          in="path",
     *          required=true,
     *          description="Document identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullMessageResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own messages.",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either message or document doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Either message can't be edited or document has been
    already attached to this message.",
     *      ),
     * )
     *
     * @param Message  $message
     * @param Document $document
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function attachDocument(Message $message, Document $document)
    {
        $this->authorize('messages.manage');
        $this->authorize('update', $message);

        $this->service->attachDocumentToMessage($message->id, $document->id);

        return FullMessageResponse::make($message);
    }

    /**
     * @OA\Delete(
     *      path="/messages/{message_id}/documents/{document_id}",
     *      tags={"Messages","Documents"},
     *      summary="Detach document from specific message",
     *      description="Allows detach a document from specific message",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          required=true,
     *          description="Message identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="document_id",
     *          in="path",
     *          required=true,
     *          description="Document identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullMessageResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to edit their own messages.",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either message or document doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Message can't be edited.",
     *      ),
     * )
     *
     * @param Message  $message
     * @param Document $document
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachDocument(Message $message, Document $document)
    {
        $this->authorize('messages.manage');
        $this->authorize('update', $message);

        $this->service->detachDocumentFromMessage($message->id, $document->id);

        return FullMessageResponse::make($message);
    }
}
