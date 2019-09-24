<?php

namespace App\Components\SiteSurvey\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class SiteSurveyQuestion
 *
 * @package App\Components\SiteSurvey\Models
 *
 * @property int     $id
 * @property string  $name
 * @property boolean $is_active
 *
 * @property-read Collection|SiteSurveyQuestionOption[] $siteSurveyQuestionOptions
 *
 * @method static Builder|SiteSurveyQuestion whereId($value)
 * @method static Builder|SiteSurveyQuestion whereName($value)
 * @method static Builder|SiteSurveyQuestion whereIsActive($value)
 * @method static Builder|SiteSurveyQuestion active()
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name", "is_active"}
 * )
 */
class SiteSurveyQuestion extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Site survey question id",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Site survey question name",
     *     type="string",
     *     example="Is this a single or double story?"
     * ),
     * @OA\Property(
     *     property="is_active",
     *     description="Indicates whether site survey question is active or not",
     *     type="boolean",
     *     example="true"
     * ),
     * @OA\Property(
     *     property="site_survey_question_options",
     *     description="List of site survey question options",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SiteSurveyQuestionOption"),
     * ),
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
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
     * Site survey question options.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function siteSurveyQuestionOptions(): HasMany
    {
        return $this->hasMany(SiteSurveyQuestionOption::class, 'site_survey_question_id');
    }

    /**
     * Scope a query to include only active site survey questions.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
