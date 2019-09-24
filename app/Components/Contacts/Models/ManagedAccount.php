<?php

namespace App\Components\Contacts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class ManagedAccount
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int                        $user_id
 * @property int                        $contact_id
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @OA\Schema (
 *     type="object",
 *     required={"user_id","contact_id"}
 * )
 */
class ManagedAccount extends Model
{
    public $incrementing = false;

    public const UPDATED_AT = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
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

    protected $fillable   = ['user_id', 'contact_id'];
    protected $primaryKey = ['user_id', 'contact_id'];
    protected $table      = 'managed_accounts';

    /**
     * Defines relationship with contact table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Defines relationship with user table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
