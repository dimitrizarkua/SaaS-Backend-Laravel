<?php

namespace App\Components\Jobs\Models;

use App\Components\Notifications\Models\UserNotification;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobUserNotification
 *
 * @property int                   $user_notification_id
 * @property int                   $job_id
 *
 * @property-read Job              $job
 * @property-read UserNotification $notification
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_id", "user_notification_id"}
 * )
 * @package App\Components\Jobs\Models
 */
class JobUserNotification extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_user_notification';
    protected $fillable   = ['job_id', 'user_notification_id'];
    protected $primaryKey = ['user_notification_id', 'job_id'];

    /**
     * @OA\Property(
     *     property="user_notification_id",
     *     description="User notification identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="job_id",
     *     description="Job identifier",
     *     type="integer",
     *     example="2"
     * )
     */

    /**
     * Defines job related to notification.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Defines user's notification relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(
            UserNotification::class,
            'id',
            'user_notification_id'
        );
    }
}
