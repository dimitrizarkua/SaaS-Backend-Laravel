<?php

namespace App\Components\Notifications\Models;

use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserNotification
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property string                     type
 * @property string                     body
 * @property \Illuminate\Support\Carbon $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property-read User                  $user
 *
 * @method static Builder|UserNotification whereUserId($value)
 * @method static Builder|UserNotification whereJobId($value)
 * @method static Builder|UserNotification whereType($value)
 * @method static Builder|UserNotification whereBody($value)
 * @method static Builder|UserNotification whereExpiresAt($value)
 * @method static Builder|UserNotification whereDeletedAt($value)
 * @method static Builder|UserNotification whereCreatedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "user_id", "type"}
 * )
 * @package App\Components\Notifications\Models
 */
class UserNotification extends Model
{
    use ApiRequestFillable, DateTimeFillable, SoftDeletes;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Notification identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="user_id",
     *     description="User identifier who will get notification",
     *     type="integer",
     *     example="2"
     * ),
     * @OA\Property(
     *     property="type",
     *     description="Notification type",
     *     type="string",
     *     example="job.created"
     * ),
     * @OA\Property(
     *     property="body",
     *     description="Notification body json string",
     *     type="string",
     *     example="{
     *      text: Notification text,
     *      sender:{
     *          first_name: First,
     *          last_name: Last,
     *          avatar: {
     *              url: \/f206ef51064433ba996486d7460f6f4c01080521a31abd66a365f0cbf17d2442
     *          }
     *      }
     *     }"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="expires_at", type="string", format="date-time"),
     * @OA\Property(property="deleted_at", type="string", format="date-time"),
     */

    const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'expires_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    protected $dates = [
        'created_at',
        'expires_at',
        'deleted_at',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'deleted_at',
    ];

    /**
     * Target user. This user will get notification and can read it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
