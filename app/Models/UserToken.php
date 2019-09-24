<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserToken
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $type
 * @property string                          $token
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @method static Builder|UserToken whereCreatedAt($value)
 * @method static Builder|UserToken whereExpiresAt($value)
 * @method static Builder|UserToken whereToken($value)
 * @method static Builder|UserToken whereType($value)
 * @method static Builder|UserToken whereUserId($value)
 *
 * @property-read \App\Models\User           $user
 * @mixin \Eloquent
 */
class UserToken extends Model
{
    const UPDATED_AT = null;

    public $incrementing = false;

    protected $table      = 'user_tokens';
    protected $fillable   = ['user_id', 'type', 'token', 'expires_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'expires_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
