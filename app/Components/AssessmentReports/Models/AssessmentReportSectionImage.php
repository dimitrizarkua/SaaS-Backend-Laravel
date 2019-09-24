<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Models\PositionableMapping;
use App\Components\Photos\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionImage
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer|null $photo_id
 * @property string|null  $caption
 * @property integer      $desired_width
 * @property-read Photo   $photo
 *
 * @method static Builder|AssessmentReportSectionImage whereId($value)
 * @method static Builder|AssessmentReportSectionImage whereAssessmentReportSectionId($value)
 * @method static Builder|AssessmentReportSectionImage wherePhotoId($value)
 * @method static Builder|AssessmentReportSectionImage whereCaption($value)
 * @method static Builder|AssessmentReportSectionImage whereDesiredWidth($value)
 * @method static Builder|AssessmentReportSectionImage whereCreatedAt($value)
 * @method static Builder|AssessmentReportSectionImage whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "assessment_report_section_id",
 *         "desired_width",
 *         "created_at",
 *         "updated_at"
 *     }
 * )
 */
class AssessmentReportSectionImage extends AssessmentReportSectionEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR section image identifier",
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
     *     property="caption",
     *     description="AR section image caption",
     *     type="string",
     *     example="Section image text",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="desired_width",
     *     description="Desired width for image",
     *     type="integer",
     *     example=512,
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

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): ?PositionableMapping
    {
        return null;
    }
}
