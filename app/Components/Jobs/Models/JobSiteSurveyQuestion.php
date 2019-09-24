<?php

namespace App\Components\Jobs\Models;

use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobSiteSurveyQuestion
 *
 * @package App\Components\Jobs\Models
 *
 * @property int    $job_id
 * @property int    $site_survey_question_id
 * @property int    $site_survey_question_option_id
 * @property string answer
 *
 * @property-read \App\Components\Jobs\Models\Job                            $job
 * @property-read \App\Components\SiteSurvey\Models\SiteSurveyQuestion       $siteSurveyQuestion
 * @property-read \App\Components\SiteSurvey\Models\SiteSurveyQuestionOption $siteSurveyQuestionOption
 * @mixin \Eloquent
 */
class JobSiteSurveyQuestion extends Model
{
    use HasCompositePrimaryKey;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps   = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_site_survey_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_id',
        'site_survey_question_id',
        'site_survey_question_option_id',
        'answer',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = [
        'job_id',
        'site_survey_question_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siteSurveyQuestion(): BelongsTo
    {
        return $this->belongsTo(SiteSurveyQuestion::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siteSurveyQuestionOption(): BelongsTo
    {
        return $this->belongsTo(SiteSurveyQuestionOption::class);
    }
}
