<?php

namespace App\Components\AssessmentReports\Models;

use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportStatus
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer               $id
 * @property integer               $assessment_report_id
 * @property integer|null          $user_id
 * @property string                $status
 * @property Carbon                $created_at
 * @property-read AssessmentReport $assessmentReport
 * @property-read User             $user
 *
 * @method static Builder|AssessmentReport whereId($value)
 * @method static Builder|AssessmentReport whereAssessmentReportId($value)
 * @method static Builder|AssessmentReport whereUserId($value)
 * @method static Builder|AssessmentReport whereStatus($value)
 * @method static Builder|AssessmentReport whereCreatedAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id", "assessment_report_id", "status", "created_at"}
 * )
 */
class AssessmentReportStatus extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="AR status identifier",
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
     *     property="user_id",
     *     description="Identifier of user",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="status",
     *     description="Status name",
     *     ref="#/components/schemas/AssessmentReportStatuses"
     * ),
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     * ),
     */

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * Relationship with assessment_reports table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assessmentReport(): BelongsTo
    {
        return $this->belongsTo(AssessmentReport::class);
    }

    /**
     * Relationship with users table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
