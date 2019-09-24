<?php

namespace App\Components\Jobs\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesTemplate
 *
 * @package App\Components\Jobs\Models
 * @mixin \Eloquent
 *
 * @property int     $id
 * @property string  $name
 * @property string  $body
 * @property boolean $active
 * *
 * @OA\Schema (
 *     type="object",
 *     required={"id","name","body","active"}
 * )
 */
class JobNotesTemplate extends Model
{
    use ApiRequestFillable;

    private const ALLOWED_TAGS = '<b><strong><i><u><ul><li><p><span><br><em>';

    /**
     * @OA\Property(property="id", type="integer", description="Template identifier", example=1),
     * @OA\Property(property="name", type="string", description="Template name", example="Job Scheduled"),
     * @OA\Property(property="body", type="string", description="Template body", example="Some text"),
     * @OA\Property(
     *     property="active",
     *     type="boolean",
     *     description="Indicates if the template is active",
     *     example="true"
     * ),
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

    /**
     * Allows to filter templates.
     *
     * @param array $options
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function search(array $options): \Illuminate\Database\Eloquent\Builder
    {
        $query = self::query();

        if (isset($options['active'])) {
            $query->where('active', '=', $options['active']);
        }

        return $query;
    }

    /**
     * @param string $value
     *
     * @return \App\Components\Jobs\Models\JobNotesTemplate
     */
    public function setBodyAttribute(string $value): self
    {
        $this->attributes['body'] = strip_tags($value, self::ALLOWED_TAGS);

        return $this;
    }
}
