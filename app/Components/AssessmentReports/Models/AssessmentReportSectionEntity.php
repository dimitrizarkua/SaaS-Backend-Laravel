<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Models\PositionableMapping;
use App\Contracts\PositionableInterface;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class AssessmentReportSectionEntity
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property int                          $id
 * @property int                          $assessment_report_section_id
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 * @property-read AssessmentReportSection $section
 *
 * @mixin \Eloquent
 */
abstract class AssessmentReportSectionEntity extends Model implements PositionableInterface
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
    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentReportSection::class, 'assessment_report_section_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): ?PositionableMapping
    {
        return new PositionableMapping($this->section()->getForeignKey());
    }
}
