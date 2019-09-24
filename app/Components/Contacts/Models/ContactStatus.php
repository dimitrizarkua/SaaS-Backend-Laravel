<?php

namespace App\Components\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class ContactStatus
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int    $id
 * @property string $status
 * @property int    $contact_id
 * @property string $created_at
 *
 * @OA\Schema (
 *     type="object",
 *     required={"id","status", "contact_id", "created_at"}
 * )
 */
class ContactStatus extends Model
{
    /**
     * @OA\Property(property="id", type="integer", description="Contact status identifier", example=1),
     * @OA\Property(property="status", type="string", description="Contact status name", example="In-Active"),
     * @OA\Property(property="contact_id", type="integer", description="Contact identifier name", example="1"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     */

    public const UPDATED_AT = null;

    protected $touches = ['contact'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

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

    /**
     * Defines relation for contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
