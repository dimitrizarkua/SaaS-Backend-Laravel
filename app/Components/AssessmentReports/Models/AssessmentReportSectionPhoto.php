<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Photos\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionPhoto
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer|null $photo_id
 * @property integer      $position
 * @property string|null  $caption
 * @property-read Photo   $photo
 *
 * @method static Builder|AssessmentReportSectionPhoto whereId($value)
 * @method static Builder|AssessmentReportSectionPhoto whereAssessmentReportSectionId($value)
 * @method static Builder|AssessmentReportSectionPhoto wherePhotoId($value)
 * @method static Builder|AssessmentReportSectionPhoto wherePosition($value)
 * @method static Builder|AssessmentReportSectionPhoto whereCaption($value)
 * @method static Builder|AssessmentReportSectionPhoto whereCreatedAt($value)
 * @method static Builder|AssessmentReportSectionPhoto whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "assessment_report_section_id",
 *         "position",
 *         "created_at",
 *         "updated_at"
 *     }
 * )
 */
class AssessmentReportSectionPhoto extends AssessmentReportSectionEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR section photo identifier",
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
     *     property="photo_id",
     *     description="Identifier of photo",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR section photo position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="caption",
     *     description="AR section photo caption",
     *     type="string",
     *     example="Section photo text",
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
     * Relationship with photos table.
     *
     * @return BelongsTo
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }
}
