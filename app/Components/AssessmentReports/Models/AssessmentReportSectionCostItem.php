<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Models\PositionableMapping;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionCostItem
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property int                           $assessment_report_cost_item_id
 * @property int                           $position
 * @property-read float                    $total_amount
 * @property-read float                    $tax
 * @property-read AssessmentReportCostItem $costItem
 *
 * @method static Builder|AssessmentReportSectionCostItem whereAssessmentReportSectionId($value)
 * @method static Builder|AssessmentReportSectionCostItem whereAssessmentReportCostItemId($value)
 * @method static Builder|AssessmentReportSectionCostItem wherePosition($value)
 * @method static Builder|AssessmentReportSectionCostItem whereCreatedAt($value)
 * @method static Builder|AssessmentReportSectionCostItem whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={
 *         "assessment_report_section_id",
 *         "assessment_report_cost_item_id",
 *         "position",
 *         "created_at",
 *         "updated_at"
 *     }
 * )
 */
class AssessmentReportSectionCostItem extends AssessmentReportSectionEntity
{
    use HasCompositePrimaryKey;

    /**
     * @OA\Property(
     *     property="assessment_report_section_id",
     *     description="Identifier of AR section",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="assessment_report_cost_item_id",
     *     description="Identifier of AR cost item",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR section cost item position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     * ),
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time",
     * ),
     */

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = [
        'assessment_report_section_id',
        'assessment_report_cost_item_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * Relationship with assessment_report_cost_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentReportCostItem::class, 'assessment_report_cost_item_id')
            ->orderBy('position');
    }

    /**
     * Returns related cost item total amount.
     *
     * @return float
     */
    public function getTotalAmountAttribute(): float
    {
        return round($this->costItem->getTotalAmount(), 2);
    }

    /**
     * Returns related cost item tax.
     *
     * @return float
     */
    public function getTaxAttribute(): float
    {
        return $this->costItem->getItemTax();
    }

    /**
     * {@inheritdoc}
     */
    public function getPositionableMapping(): PositionableMapping
    {
        return new PositionableMapping(
            $this->section()->getForeignKey(),
            'assessment_report_cost_item_id'
        );
    }
}
