<?php

namespace App\Components\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class MessageStatus
 *
 * @package App\Components\Messages\Models
 *
 * @mixin \Eloquent
 * @property int                        $id
 * @property int                        $message_id
 * @property string                     $status
 * @property string|null                $reason
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","message_id","status","created_at"}
 * )
 */
class MessageStatus extends Model
{
    const UPDATED_AT = null;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="message_id", type="integer", example=2)
     * @OA\Property(
     *     property="status",
     *     description="Message status",
     *     type="string",
     *     enum={"draft","ready_for_delivery","forwarded_for_delivery","delivered","delivery_failed"},
     *     example="draft"
     * )
     * @OA\Property(
     *     property="reason",
     *     description="Optional reason for status change",
     *     type="string",
     *     example="Reason",
     *     nullable=true
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * Get message record associated with the status.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'message_id',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];
}
