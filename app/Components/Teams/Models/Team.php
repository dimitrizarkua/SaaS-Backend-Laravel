<?php

namespace App\Components\Teams\Models;

use App\Components\Jobs\Models\Job;
use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Class Team
 *
 * @property int                    $id
 * @property string                 $name
 * @property-read Collection|User[] $users
 * @property-read Collection|Job[]  $jobs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name"}
 * )
 *
 * @mixin \Eloquent
 */
class Team extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="name",
     *     description="Name of the team",
     *     type="string",
     *     example="Dream team"
     * )
     */

    public $timestamps = false;


    protected $guarded = [
        'id',
    ];

    /**
     * All users of the team relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_user',
            'team_id',
            'user_id'
        );
    }

    /**
     * Assigned jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_team_assignments',
            'team_id',
            'job_id'
        );
    }
}
