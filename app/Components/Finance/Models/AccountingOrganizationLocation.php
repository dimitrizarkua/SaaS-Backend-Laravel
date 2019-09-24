<?php

namespace App\Components\Finance\Models;

use App\Components\Locations\Models\Location;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AccountingOrganizationLocation
 *
 * @property int                         $accounting_organization_id
 * @property int                         $location_id
 * @property-read Location               $location
 * @property-read AccountingOrganization $accountingOrganization
 *
 * @method static Builder|AccountingOrganizationLocation newModelQuery()
 * @method static Builder|AccountingOrganizationLocation newQuery()
 * @method static Builder|AccountingOrganizationLocation query()
 * @method static Builder|AccountingOrganization whereLocationId($value)
 * @method static Builder|AccountingOrganization whereAccountingOrganizationId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"location_id","accounting_organization_id"}
 * )
 */
class AccountingOrganizationLocation extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $table      = 'accounting_organization_locations';
    protected $fillable   = ['accounting_organization_id', 'location_id'];
    protected $primaryKey = ['accounting_organization_id', 'location_id'];

    /**
     * Defines relationship with location table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Defines relationship with accounting organization table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingOrganization(): BelongsTo
    {
        return $this->belongsTo(AccountingOrganization::class);
    }
}
