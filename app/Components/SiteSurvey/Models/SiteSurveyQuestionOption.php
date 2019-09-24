<?php

namespace App\Components\SiteSurvey\Models;

use App\Components\Jobs\Models\Job;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SiteSurveyQuestionOption
 *
 * @package App\Components\SiteSurvey\Models
 *
 * @property int    $id
 * @property int    $site_survey_question_id
 * @property string $name
 *
 * @property-read SiteSurveyQuestion $siteSurveyQuestion
 * @property-read Collection|Job[]   $jobs
 *
 * @method static Builder|SiteSurveyQuestion whereId($value)
 * @method static Builder|SiteSurveyQuestion whereSiteSurveyQuestionId($value)
 * @method static Builder|SiteSurveyQuestion whereName($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"site_survey_question_id, name"}
 * )
 */
class SiteSurveyQuestionOption extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Site survey question option id",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="site_survey_question_id",
     *     description="Site survey question id",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Site survey question option name",
     *     type="string",
     *     example="Yes"
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
     * Associated site survey question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siteSurveyQuestion(): BelongsTo
    {
        return $this->belongsTo(SiteSurveyQuestion::class);
    }

    /**
     * Returns attached jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_site_survey_options',
            'job_id',
            'site_survey_question_option_id'
        );
    }
}
