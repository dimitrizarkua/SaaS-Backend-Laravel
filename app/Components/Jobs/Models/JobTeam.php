<?php

namespace App\Components\Jobs\Models;

use App\Components\Teams\Models\Team;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobTeam
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                    $job_id
 * @property int                                    $team_id
 * @property-read \App\Components\Jobs\Models\Job   $job
 * @property-read \App\Components\Teams\Models\Team $team
 */
class JobTeam extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_team_assignments';
    protected $fillable   = ['job_id', 'team_id'];
    protected $primaryKey = ['job_id', 'team_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
