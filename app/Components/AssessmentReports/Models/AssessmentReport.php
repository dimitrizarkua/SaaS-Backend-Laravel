<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Exceptions\InvalidArgumentException;
use App\Components\Documents\Models\Document;
use App\Components\Jobs\Models\Job;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReport
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer                                        $id
 * @property integer                                        $job_id
 * @property integer                                        $user_id
 * @property integer|null                                   $document_id
 * @property string|null                                    $heading
 * @property string|null                                    $subheading
 * @property Carbon                                         $date
 * @property Carbon                                         $created_at
 * @property Carbon                                         $updated_at
 * @property Carbon|null                                    $deleted_at
 * @property-read Job                                       $job
 * @property-read User                                      $user
 * @property-read Document                                  $document
 * @property-read Collection|AssessmentReportStatus[]       $statuses
 * @property-read AssessmentReportStatus                    $latestStatus
 * @property-read Collection|AssessmentReportSection[]      $sections
 * @property-read Collection|AssessmentReportCostingStage[] $costingStages
 * @property-read Collection|AssessmentReportCostItem[]     $costItems
 *
 * @method static Builder|AssessmentReport whereId($value)
 * @method static Builder|AssessmentReport whereJobId($value)
 * @method static Builder|AssessmentReport whereUserId($value)
 * @method static Builder|AssessmentReport whereHeading($value)
 * @method static Builder|AssessmentReport whereSubheading($value)
 * @method static Builder|AssessmentReport whereDate($value)
 * @method static Builder|AssessmentReport whereCreatedAt($value)
 * @method static Builder|AssessmentReport whereUpdatedAt($value)
 * @method static Builder|AssessmentReport whereDeletedAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id", "job_id", "user_id", "date", "created_at", "updated_at"}
 * )
 */
class AssessmentReport extends Model
{
    use ApiRequestFillable, SoftDeletes, DateTimeFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="AR identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="job_id",
     *     description="Identifier of job",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="user_id",
     *     description="Identifier of user",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="document_id",
     *     description="Identifier of document",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="heading",
     *     description="Heading of AR",
     *     type="string",
     *     example="Heading",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="subheading",
     *     description="Subheading of AR",
     *     type="string",
     *     example="Subheading",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="date",
     *     description="Date",
     *     type="string",
     *     format="date",
     *     example="2019-02-28",
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
     * @OA\Property(
     *     property="deleted_at",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
     * ),
     */

    public const PRINT_VIEW = 'assessmentReports.print';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date'       => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relationship with jobs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
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

    /**
     * Relationship with documents table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Relationship with assessment_report_statuses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(AssessmentReportStatus::class);
    }

    /**
     * Relationship with assessment_report_sections table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(AssessmentReportSection::class)
            ->orderBy('position');
    }

    /**
     * Relationship with assessment_report_costing_stages table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costingStages(): HasMany
    {
        return $this->hasMany(AssessmentReportCostingStage::class)
            ->orderBy('position');
    }

    /**
     * Relationship with assessment_report_cost_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(AssessmentReportCostItem::class)
            ->orderBy('position');
    }

    /**
     * Returns assessment report total amount.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        $totalAmount = $this->costItems->reduce(function (float $carry, AssessmentReportCostItem $costItem) {
            return $carry + $costItem->getTotalAmount();
        }, 0);

        return round($totalAmount, 2);
    }

    /**
     * Returns tax for assessment report.
     *
     * @return float
     */
    public function getTax(): float
    {
        $tax = $this->costItems()
            ->with('taxRate')
            ->get()
            ->reduce(function (float $carry, AssessmentReportCostItem $costItem) {
                return $carry + $costItem->getItemTax();
            }, 0);

        return round($tax, 2);
    }

    /**
     * Generates and returns name for PDF file.
     *
     * @return string
     */
    public function generatePDFName(): string
    {
        return sprintf(
            '%s#%d(%s).pdf',
            str_singular($this->getTable()),
            $this->id,
            now()->format('d-m-Y')
        );
    }

    /**
     * The latest (or in other words, current) assessment report status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this->hasOne(AssessmentReportStatus::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Setter for date field.
     *
     * @param Carbon|string $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setDateAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('date', $datetime);
    }

    /**
     * Indicates whether assessment report is approved or not.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return AssessmentReportStatuses::CLIENT_APPROVED === $this->latestStatus->status;
    }

    /**
     * Indicates whether assessment report is cancelled or not.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return in_array($this->latestStatus->status, AssessmentReportStatuses::$cancelledStatuses);
    }

    /**
     * Allows to change status of the assessment report.
     *
     * @param string $status New status.
     * @param int    $userId Id of user who is changing status.
     *
     * @return AssessmentReportStatus
     *
     * @throws InvalidArgumentException
     */
    public function changeStatus(string $status, int $userId): AssessmentReportStatus
    {
        if (!in_array($status, AssessmentReportStatuses::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', AssessmentReportStatuses::values())
            ));
        }

        /** @var AssessmentReportStatus $status */
        $status = $this->statuses()->create([
            'status'  => $status,
            'user_id' => $userId,
        ]);

        return $status;
    }
}
