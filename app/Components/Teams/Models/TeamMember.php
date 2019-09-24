<?php

namespace App\Components\Teams\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TeamMember
 *
 * @package App\Components\Team\Models
 *
 * @mixin \Eloquent
 * @property int                                    $team_id
 * @property int                                    $user_id
 * @property-read \App\Components\Teams\Models\Team $team
 * @property-read \App\Models\User                  $user
 */
class TeamMember extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'team_user';
    protected $fillable = ['team_id', 'user_id'];
    protected $primaryKey = ['team_id', 'user_id'];

    /**
     * Define relationship with teams table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Define relationship with users table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
