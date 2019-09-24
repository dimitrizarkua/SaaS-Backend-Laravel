<?php

namespace App\Components\Contacts\Models;

use App\Components\Notifications\Models\UserNotification;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ContactUserNotification
 *
 * @property int                   $user_notification_id
 * @property int                   $contact_id
 *
 * @property-read Contact          $contact
 * @property-read UserNotification $notification
 *
 * @OA\Schema(
 *     type="object",
 *     required={"contact_id", "user_notification_id"}
 * )
 * @package App\Components\Contacts\Models
 */
class ContactUserNotification extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'contact_user_notification';
    protected $fillable   = ['contact_id', 'user_notification_id'];
    protected $primaryKey = ['user_notification_id', 'contact_id'];

    /**
     * @OA\Property(
     *     property="user_notification_id",
     *     description="User notification identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="contact_id",
     *     description="Contact identifier",
     *     type="integer",
     *     example="2"
     * )
     */

    /**
     * Defines contact related to notification.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
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
