<?php

namespace App\Components\AssessmentReports\Models;

use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionTextBlock
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer     $position
 * @property string|null $text
 *
 * @method static Builder|AssessmentReportSectionTextBlock whereId($value)
 * @method static Builder|AssessmentReportSectionTextBlock whereAssessmentReportSectionId($value)
 * @method static Builder|AssessmentReportSectionTextBlock wherePosition($value)
 * @method static Builder|AssessmentReportSectionTextBlock whereText($value)
 * @method static Builder|AssessmentReportSectionTextBlock whereCreatedAt($value)
 * @method static Builder|AssessmentReportSectionTextBlock whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={"id", "assessment_report_section_id", "position", "created_at", "updated_at"}
 * )
 */
class AssessmentReportSectionTextBlock extends AssessmentReportSectionEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR section text block identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="assessment_report_section_id",
     *     description="Identifier of AR section",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR section text block position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="text",
     *     description="AR section text block text",
     *     type="string",
     *     example="Section text block text",
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
}
