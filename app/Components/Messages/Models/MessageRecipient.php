<?php

namespace App\Components\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class MessageRecipient
 *
 * @package App\Components\Messages\Models
 *
 * @mixin \Eloquent
 * @property int                        $id
 * @property int                        $message_id
 * @property string                     $name
 * @property string                     $address
 * @property string                     $type
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","message_id","address","type","created_at"}
 * )
 */
class MessageRecipient extends Model
{
    const UPDATED_AT = null;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="message_id", type="integer", example=2)
     * @OA\Property(
     *     property="type",
     *     description="Recipient type",
     *     type="string",
     *     enum={"to","cc","bcc"},
     *     example="to"
     * )
     * @OA\Property(
     *     property="name",
     *     description="Message recipient name",
     *     type="string",
     *     example="John Doe"
     * )
     * @OA\Property(
     *     property="address",
     *     description="Message recipient address, e.g. email address or phone number",
     *     type="string",
     *     example="person@example.com"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * Get message record associated with the status.
     */
    public function message()
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
