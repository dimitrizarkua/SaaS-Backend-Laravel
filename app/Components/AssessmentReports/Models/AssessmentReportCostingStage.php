<?php

namespace App\Components\AssessmentReports\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostingStage
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property string                                     $name
 * @property-read Collection|AssessmentReportCostItem[] $costItems
 *
 * @method static Builder|AssessmentReportCostingStage whereId($value)
 * @method static Builder|AssessmentReportCostingStage whereAssessmentReportId($value)
 * @method static Builder|AssessmentReportCostingStage whereName($value)
 * @method static Builder|AssessmentReportCostingStage wherePosition($value)
 * @method static Builder|AssessmentReportCostingStage whereCreatedAt($value)
 * @method static Builder|AssessmentReportCostingStage whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={"id", "assessment_report_id", "name", "position", "created_at", "updated_at"}
 * )
 */
class AssessmentReportCostingStage extends AssessmentReportEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR costing stage identifier",
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
     *     property="name",
     *     description="Name of costing stage",
     *     type="string",
     *     example="Stage 1",
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR costing stage position",
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costItems(): HasMany
    {
        return $this->HasMany(AssessmentReportCostItem::class, 'assessment_report_costing_stage_id');
    }
}
