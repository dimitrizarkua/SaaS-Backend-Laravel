<?php

namespace App\Components\Jobs\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class JobService
 *
 * @property int                   $id
 * @property string                $name
 *
 * @property-read Collection|Job[] $jobs
 *
 * @method static Builder|JobService whereId($value)
 * @method static Builder|JobService whereName($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"}
 * )
 */
class JobService extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Service Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Service name",
     *     type="string",
     *     example="name"
     * ),
     */

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Associated jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
