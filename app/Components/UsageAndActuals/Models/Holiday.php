<?php

namespace App\Components\UsageAndActuals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Holiday
 *
 * @mixin \Eloquent
 *
 * @property int         $id
 * @property string|null $name
 * @property Carbon      $date
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "id",
 *         "name",
 *         "date",
 *         "created_at",
 *         "updated_at",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class Holiday extends Model
{
    /**
     * @OA\Property(property="id", type="integer", description="Model identifier", example=1),
     * @OA\Property(property="name", type="string", description="Name of holiday", example="New Year"),
     * @OA\Property(property="date", type="string", format="date-time"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     */

    public $timestamps = true;

    protected $table = 'holidays';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'date'       => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];
}
