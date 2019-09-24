<?php

namespace App\Components\Locations\Models;

use App\Components\Addresses\Models\Suburb;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Operations\Models\JobRun;
use App\Components\Operations\Models\JobRunTemplate;
use App\Components\Operations\Models\Vehicle;
use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class Location
 * Aka Branch, Franchise.
 *
 * @package App\Components\Locations\Models
 *
 * @mixin \Eloquent
 * @property int                                      $id
 * @property string                                   $name
 * @property string                                   $code
 * @property int                                      $tz_offset
 * @property-read Collection|\App\Models\User[]       $users
 * @property-read Collection|Suburb[]                 $suburbs
 * @property-read Collection|Job[]                    $ownedJobs
 * @property-read Collection|Job[]                    $assignedJobs
 * @property-read Collection|AccountingOrganization[] $accountingOrganizations
 * @property-read Collection|JobRun[]                 $runs
 * @property-read Collection|JobRunTemplate[]         $runTemplates
 * @property-read Collection|Vehicle[]                $vehicles
 * @property-read Collection|PurchaseOrder[]          $purchaseOrders
 *
 * @OA\Schema(
 *     type="object",
 *     nullable=true,
 *     required={"id","name","code"}
 * )
 *
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location query()
 * @method static Builder|Location whereCode($value)
 * @method static Builder|Location whereId($value)
 * @method static Builder|Location whereName($value)
 */
class Location extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="name",
     *     description="Location name.",
     *     type="string",
     *     example="Sydney",
     * )
     * @OA\Property(
     *     property="code",
     *     description="Location code.",
     *     type="string",
     *     example="SYD",
     * )
     * @OA\Property(
     *     property="tz_offset",
     *     description="Timezone offset in minutes.",
     *     type="integer",
     *     example=120,
     * )
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['users'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Users that belongs to location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                User::class,
                'location_user',
                'location_id',
                'user_id'
            )
            ->withPivot('primary');
    }

    /**
     * Related suburbs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function suburbs(): BelongsToMany
    {
        return $this->belongsToMany(
            Suburb::class,
            'location_suburb',
            'location_id',
            'suburb_id'
        );
    }

    /**
     * Jobs that this location owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedJobs(): HasMany
    {
        return $this->hasMany(
            Job::class,
            'owner_location_id'
        );
    }

    /**
     * Jobs that this was assigned to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedJobs(): HasMany
    {
        return $this->hasMany(
            Job::class,
            'assigned_location_id'
        );
    }

    /**
     * Accounting organizations associated with the location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accountingOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(
            AccountingOrganization::class,
            'accounting_organization_locations',
            'location_id',
            'accounting_organization_id'
        );
    }

    /**
     * Job runs related to the location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runs(): HasMany
    {
        return $this->hasMany(
            JobRun::class,
            'location_id'
        );
    }

    /**
     * Job run templates related to the location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runTemplates(): HasMany
    {
        return $this->hasMany(
            JobRunTemplate::class,
            'location_id'
        );
    }

    /**
     * Vehicles owned by the location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(
            Vehicle::class,
            'location_id'
        );
    }

    /**
     * Relationship with purchase orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'location_id');
    }
}
