<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Models\PositionableMapping;
use App\Contracts\PositionableInterface;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class AssessmentReportEntity
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property int                   $id
 * @property int                   $assessment_report_id
 * @property int                   $position
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 * @property-read AssessmentReport $assessmentReport
 *
 * @mixin \Eloquent
 */
abstract class AssessmentReportEntity extends Model implements PositionableInterface
{
    use ApiRequestFillable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relationship with assessment_report_sections table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assessmentReport(): BelongsTo
    {
        return $this->belongsTo(AssessmentReport::class, 'assessment_report_id');
    }

    /**
    * {@inheritDoc}
     */
    public function getPositionableMapping(): ?PositionableMapping
    {
        return new PositionableMapping($this->assessmentReport()->getForeignKey());
    }
}
