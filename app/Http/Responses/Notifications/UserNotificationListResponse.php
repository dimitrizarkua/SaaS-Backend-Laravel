<?php

namespace App\Http\Responses\Notifications;

use App\Http\Responses\ApiOKResponse;

/**
 * Class UserNotificationListResponse
 *
 * @package App\Http\Responses\UserNotification
 *
 * @OA\Schema(required={"data"})
 */
class UserNotificationListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserNotification")
     * )
     *
     * @var \App\Components\Notifications\Models\UserNotification[]
     */
    protected $data;
}
