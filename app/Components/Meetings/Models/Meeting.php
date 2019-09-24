<?php

namespace App\Components\Meetings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class Meeting
 *
 * @package App\Components\Meetings\Models
 *
 * @mixin \Eloquent
 * @property int                        $id
 * @property int                        $user_id
 * @property string                     $title
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User      $user
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","title", "user_id","scheduled_at", "created_at"}
 * )
 */
class Meeting extends Model
{
    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="title",
     *     description="Title of the meeting",
     *     type="string",
     *     example="Weekly meeting"
     * )
     * @OA\Property(
     *     property="user_id",
     *     description="Meeting scheduler id",
     *     type="integer",
     *     example=573187
     * )
     * @OA\Property(property="scheduled_at", type="string", format="date-time")
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $casts = [
        'created_at'   => 'datetime:Y-m-d\TH:i:s\Z',
        'scheduled_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    protected $dates = [
        'created_at',
        'scheduled_at',
    ];

    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * Define user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse scheduled_at from request to valid format.
     *
     * @param string $date date from request
     * @return void
     */
    public function setScheduledAtAttribute($date): void
    {
        $this->attributes['scheduled_at'] = Carbon::parse($date);
    }
}
