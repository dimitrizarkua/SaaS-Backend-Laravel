<?php

namespace App\Http\Controllers;

use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use App\Core\JsonModel;
use App\Http\Responses\ApiOKResponse;
use App\Jobs\Messages\HandleExternalMessageStatusUpdate;
use App\Jobs\Messages\HandleIncomingJobMessage;
use Illuminate\Http\Request;

/**
 * Class WebhooksController
 *
 * @package App\Http\Controllers
 */
class WebhooksController extends Controller
{
    /**
     * @var \App\Components\Messages\Interfaces\MessageDeliveryServiceInterface
     */
    private $service;

    /**
     * DocumentsController constructor.
     *
     * @param MessageDeliveryServiceInterface $service
     */
    public function __construct(MessageDeliveryServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Handles message status update from Mailgun.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function handleMailgunMessageStatusUpdateWebhook(Request $request)
    {
        if (empty($request->getContent())) {
            return new ApiOKResponse();
        }

        $content = JsonModel::replaceHyphensWithUnderscores($request->getContent());
        $content = \GuzzleHttp\json_decode($content, true);

        HandleExternalMessageStatusUpdate::dispatch(MessageTypes::EMAIL, $content)->onQueue('messages');

        return new ApiOKResponse();
    }

    /**
     * Handles incoming message to `contracts` mailbox from Mailgun.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     */
    public function handleMailgunIncomingJobMessageWebhook(Request $request)
    {
        if (empty($request->getContent())) {
            return new ApiOKResponse();
        }

        // An important note about incoming email processing from Mailgun.
        // We assume that `Store & Notify` technique is used for incoming email processing,
        // not `Forward`!
        //
        // This is very important, because with `Forward` technique, Mailgun sends
        // POST request with `Content-Type: multipart/form-data` directly to the endpoint specified
        // and since email can contain attachments (up to 25 Mb in size), the only proper way of
        // saving a message on our side would be to process it right here, not in the background
        // because pushing such a big messages to the queue and then manually parse
        // `multipart/form-data` seems odd and violates best practicies of working with queues.
        // That is why our backend needs to be notified about new messages and not directly receiving
        // them.
        // With `Store & Notify` technique our backend get notified about incoming messages, pushes
        // processing task to the queue and receives message with attachments via Mailgun API
        // in the background.

        $content = JsonModel::replaceHyphensWithUnderscores(json_encode($request->post()));
        $content = \GuzzleHttp\json_decode($content, true);

        HandleIncomingJobMessage::dispatch($content)->onQueue('messages');

        return new ApiOKResponse();
    }
}
