<?php

namespace App\Components\Locations\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LocationUser
 *
 * @package App\Components\Locations\Models
 *
 * @mixin \Eloquent
 * @property int                                            $location_id
 * @property int                                            $user_id
 * @property bool                                           $primary
 * @property-read \App\Components\Locations\Models\Location $location
 * @property-read \App\Models\User                          $user
 */
class LocationUser extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'location_user';
    protected $fillable   = ['location_id', 'user_id', 'primary'];
    protected $primaryKey = ['location_id', 'user_id'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
