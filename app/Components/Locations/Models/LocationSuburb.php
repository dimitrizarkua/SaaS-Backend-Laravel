<?php

namespace App\Components\Locations\Models;

use App\Components\Addresses\Models\Suburb;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LocationSuburb
 *
 * @package App\Components\Locations\Models
 *
 * @mixin \Eloquent
 * @property int                                            $location_id
 * @property int                                            $suburb_id
 * @property-read \App\Components\Locations\Models\Location $location
 * @property-read \App\Components\Addresses\Models\Suburb   $suburb
 */
class LocationSuburb extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'location_suburb';
    protected $fillable   = ['location_id', 'suburb_id'];
    protected $primaryKey = ['location_id', 'suburb_id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function suburb()
    {
        return $this->belongsTo(Suburb::class);
    }
}
