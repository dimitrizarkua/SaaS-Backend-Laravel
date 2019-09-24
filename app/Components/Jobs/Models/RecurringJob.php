<?php

namespace App\Components\Jobs\Models;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecurringJob
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 *
 * @property int                                            $id
 * @property string                                         $recurrence_rule
 * @property int                                            $insurer_id
 * @property int                                            $job_service_id
 * @property int                                            $site_address_id
 * @property int                                            $owner_location_id
 * @property string                                         $description
 * @property Carbon|null                                    $deleted_at
 *
 * @property-read \App\Components\Jobs\Models\Job           $job
 * @property-read \App\Components\Contacts\Models\Contact   $insurer
 * @property-read \App\Components\Jobs\Models\JobService    $service
 * @property-read \App\Components\Addresses\Models\Address  $siteAddress
 * @property-read \App\Components\Locations\Models\Location $ownerLocation
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *     "id","recurrence_rule", "insurer_id","job_service_id", "site_address_id", "owner_location_id", "description"
 * }
 * )
 */
class RecurringJob extends Model
{
    use ApiRequestFillable, SoftDeletes;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="recurrence_rule",
     *     description="Recurrence rule according to https://tools.ietf.org/html/rfc5545.",
     *     type="string",
     *     example="FREQ=YEARLY;INTERVAL=2;COUNT=3"
     * )
     * @OA\Property(
     *     property="insurer_id",
     *     description="Insurer contact identifier.",
     *     type="integer",
     *     example=573187
     * )
     * @OA\Property(
     *     property="job_service_id",
     *     description="Identifier of related service.",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="site_address_id",
     *     description="Identifier of site address.",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="owner_location_id",
     *     description="Identifier of owner location.",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Recurring job description.",
     *     type="string",
     *     example="Description"
     * ),
     * @OA\Property(property="deleted_at", type="string", format="date-time"),
     */

    public $incrementing = true;
    public $timestamps   = false;

    protected $guarded = ['id', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'id', 'recurring_job_id');
    }

    /**
     * Insurer company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'insurer_id');
    }

    /**
     * Job service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(JobService::class, 'job_service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siteAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'site_address_id');
    }

    /**
     * Owner location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ownerLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'owner_location_id');
    }
}
