<?php

namespace App\Components\Jobs\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class JobContactAssignmentType
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int     $id
 * @property string  $name
 * @property boolean $is_unique
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name"}
 * )
 */
class JobContactAssignmentType extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="name",
     *     description="Assignment name.",
     *     type="string",
     *     example="Loss Adjustor",
     * )
     * @OA\Property(
     *     property="is_unique",
     *     description="Indicates whether only one assignment of that type is possible or not.",
     *     type="boolean",
     *     example="true",
     * )
     */

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];
}
