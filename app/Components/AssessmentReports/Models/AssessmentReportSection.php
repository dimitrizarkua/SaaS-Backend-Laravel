<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionCostsCostSummaryData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSection
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property string                                             $type
 * @property string|null                                        $heading
 * @property string|null                                        $heading_style
 * @property integer|null                                       $heading_color
 * @property string|null                                        $text
 * @property-read AssessmentReportSectionCostsCostSummaryData   $cost_summary
 * @property-read Collection|AssessmentReportSectionTextBlock[] $textBlocks
 * @property-read AssessmentReportSectionImage                  $image
 * @property-read Collection|AssessmentReportSectionPhoto[]     $photos
 * @property-read Collection|AssessmentReportSectionCostItem[]  $costItems
 * @property-read AssessmentReportSectionRoom                   $room
 *
 * @method static Builder|AssessmentReportSection whereId($value)
 * @method static Builder|AssessmentReportSection whereAssessmentReportId($value)
 * @method static Builder|AssessmentReportSection whereType($value)
 * @method static Builder|AssessmentReportSection wherePosition($value)
 * @method static Builder|AssessmentReportSection whereHeading($value)
 * @method static Builder|AssessmentReportSection whereHeadingStyle($value)
 * @method static Builder|AssessmentReportSection whereHeadingColor($value)
 * @method static Builder|AssessmentReportSection whereText($value)
 * @method static Builder|AssessmentReportSection whereCreatedAt($value)
 * @method static Builder|AssessmentReportSection whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={"id", "assessment_report_id", "type", "position", "created_at", "updated_at"}
 * )
 */
class AssessmentReportSection extends AssessmentReportEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR section identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="assessment_report_id",
     *     description="Identifier of AR",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="type",
     *     description="AR section type",
     *     ref="#/components/schemas/AssessmentReportSectionTypes"
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR section position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="heading",
     *     description="AR section heading",
     *     type="string",
     *     example="Section heading",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="heading_style",
     *     description="AR section heading style",
     *     ref="#/components/schemas/AssessmentReportHeadingStyles",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="heading_color",
     *     description="AR section heading color",
     *     type="integer",
     *     example=16777215,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="text",
     *     description="AR section text",
     *     type="string",
     *     example="Section text",
     *     nullable=true,
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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * Relationship with assessment_report_section_text_blocks table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function textBlocks(): HasMany
    {
        return $this->hasMany(AssessmentReportSectionTextBlock::class)
            ->orderBy('position');
    }

    /**
     * Relationship with assessment_report_section_images table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function image(): HasOne
    {
        return $this->hasOne(AssessmentReportSectionImage::class);
    }

    /**
     * Relationship with assessment_report_section_photos table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(AssessmentReportSectionPhoto::class)
            ->orderBy('position');
    }

    /**
     * Relationship with assessment_report_section_cost_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(AssessmentReportSectionCostItem::class)
            ->orderBy('position');
    }

    /**
     * Relationship with assessment_report_section_rooms table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function room(): HasOne
    {
        return $this->hasOne(AssessmentReportSectionRoom::class);
    }

    /**
     * Returns cost summary of assessment report section.
     *
     * @return AssessmentReportSectionCostsCostSummaryData
     *
     * @throws \JsonMapper_Exception
     */
    public function getCostSummaryAttribute(): AssessmentReportSectionCostsCostSummaryData
    {
        if (AssessmentReportSectionTypes::COSTS !== $this->type) {
            return new AssessmentReportSectionCostsCostSummaryData();
        }

        /** @var AssessmentReportSectionCostsCostSummaryData $totalAmount */
        $totalAmount = $this->costItems->reduce(
            function (
                AssessmentReportSectionCostsCostSummaryData $summary,
                AssessmentReportSectionCostItem $sectionCostItem
            ) {
                return $summary->incrementSubTotal($sectionCostItem->total_amount)
                    ->incrementGST($sectionCostItem->tax);
            },
            new AssessmentReportSectionCostsCostSummaryData()
        );
        $totalAmount->calculateCostSummary();

        return $totalAmount;
    }
}
