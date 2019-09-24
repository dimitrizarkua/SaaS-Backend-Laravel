<?php

namespace App\Http\Controllers\Users;

use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notifications\UserNotificationListResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserNotificationsController
 *
 * @package App\Http\Controllers\Users\UserNotifications
 */
class UserNotificationsController extends Controller
{
    /** @var \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface */
    private $service;

    /**
     * UserNotificationsController constructor.
     *
     * @param \App\Components\Notifications\Interfaces\UserNotificationsServiceInterface $notificationService
     */
    public function __construct(UserNotificationsServiceInterface $notificationService)
    {
        $this->service = $notificationService;
    }

    /**
     * @OA\Get(
     *      path="/me/notifications",
     *      tags={"Users"},
     *      summary="Returns list of notifications.",
     *      description="Returns list of notifications sorted in reverse chronological order. Unread notifications will
     *      be on the top of the list.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserNotificationListResponse")
     *      )
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function listUnreadNotifications()
    {
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = UserNotification::query()
            ->withTrashed()
            ->where('user_id', Auth::id())
            ->orderByRaw('deleted_at DESC nulls first, created_at DESC')
            ->paginate(Paginator::resolvePerPage());

        return UserNotificationListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Delete(
     *      path="/me/notifications",
     *      tags={"Users"},
     *      summary="Allows to mark all notifications as read for authenticated user.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Notification doesn't exist.",
     *      )
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function readAll()
    {
        $this->service->readAll(Auth::id());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/me/notifications/{notification_id}",
     *      tags={"Users"},
     *      summary="Allows to mark as read specified notification.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="notification_id",
     *          in="path",
     *          required=true,
     *          description="Notification identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *         response="403",
     *         description="Forbidden.",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Notification doesn't exist.",
     *      )
     * )
     * @param int $notificationId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function read(int $notificationId)
    {
        $jobNotification = UserNotification::findOrFail($notificationId);
        $this->authorize('isOwner', $jobNotification);

        $this->service->read($notificationId);

        return ApiOKResponse::make();
    }
}
