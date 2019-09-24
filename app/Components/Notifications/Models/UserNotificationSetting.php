<?php

namespace App\Components\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

/**
 * Class UserNotificationSetting
 *
 * @property int       $user_id
 * @property string    $type
 * @property bool      $value
 *
 * @property-read User $user
 *
 * @method static Builder|UserNotificationSetting whereUserId($value)
 * @method static Builder|UserNotificationSetting whereType($value)
 * @method static Builder|UserNotificationSetting whereValue($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"user_id", "type", "value"}
 * )
 * @package App\Components\Notifications\Models
 */
class UserNotificationSetting extends Model
{

    public $incrementing = false;
    public $primaryKey   = ['user_id', 'type'];

    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $casts = [
        'value' => 'boolean',
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
