<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Models\PositionableMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionRoom
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property string                      $name
 * @property integer|null                $flooring_type_id
 * @property integer|null                $flooring_subtype_id
 * @property float|null                  $dimensions_length
 * @property float|null                  $dimensions_width
 * @property float|null                  $dimensions_height
 * @property float|null                  $dimensions_affected_length
 * @property float|null                  $dimensions_affected_width
 * @property bool                        $underlay_required
 * @property integer|null                $underlay_type_id
 * @property string|null                 $underlay_type_note
 * @property float|null                  $dimensions_underlay_length
 * @property float|null                  $dimensions_underlay_width
 * @property bool                        $trims_required
 * @property string|null                 $trim_type
 * @property bool                        $restorable
 * @property integer|null                $non_restorable_reason_id
 * @property integer|null                $carpet_type_id
 * @property integer|null                $carpet_construction_type_id
 * @property integer|null                $carpet_age_id
 * @property integer|null                $carpet_face_fibre_id
 * @property-read FlooringType           $flooringType
 * @property-read FlooringSubtype        $flooringSubtype
 * @property-read UnderlayType           $underlayType
 * @property-read NonRestorableReason    $nonRestorableReason
 * @property-read CarpetType             $carpetType
 * @property-read CarpetConstructionType $carpetConstructionType
 * @property-read CarpetAge              $carpetAge
 * @property-read CarpetFaceFibre        $carpetFaceFibre
 *
 * @method static Builder|AssessmentReportSectionRoom whereId($value)
 * @method static Builder|AssessmentReportSectionRoom whereAssessmentReportSectionId($value)
 * @method static Builder|AssessmentReportSectionRoom whereName($value)
 * @method static Builder|AssessmentReportSectionRoom whereFlooringTypeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereFlooringSubtypeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereDimensionsLength($value)
 * @method static Builder|AssessmentReportSectionRoom whereDimensionsWidth($value)
 * @method static Builder|AssessmentReportSectionRoom whereDimensionsHeight($value)
 * @method static Builder|AssessmentReportSectionRoom whereAffectedLength($value)
 * @method static Builder|AssessmentReportSectionRoom whereAffectedWidth($value)
 * @method static Builder|AssessmentReportSectionRoom whereUnderlayRequired($value)
 * @method static Builder|AssessmentReportSectionRoom whereUnderlayTypeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereUnderlayTypeNote($value)
 * @method static Builder|AssessmentReportSectionRoom whereDimensionsUnderlayLength($value)
 * @method static Builder|AssessmentReportSectionRoom whereDimensionsUnderlayWidth($value)
 * @method static Builder|AssessmentReportSectionRoom whereTrimsRequired($value)
 * @method static Builder|AssessmentReportSectionRoom whereTrimType($value)
 * @method static Builder|AssessmentReportSectionRoom whereRestorable($value)
 * @method static Builder|AssessmentReportSectionRoom whereNonRestorableReasonId($value)
 * @method static Builder|AssessmentReportSectionRoom whereCarpetTypeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereCarpetConstructionTypeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereCarpetAgeId($value)
 * @method static Builder|AssessmentReportSectionRoom whereCarpetFaceFibreId($value)
 * @method static Builder|AssessmentReportSectionRoom whereCreatedAt($value)
 * @method static Builder|AssessmentReportSectionRoom whereUpdatedAt($value)
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "assessment_report_section_id",
 *         "name",
 *         "underlay_required",
 *         "trims_required",
 *         "restorable",
 *         "created_at",
 *         "updated_at"
 *     }
 * )
 */
class AssessmentReportSectionRoom extends AssessmentReportSectionEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR section room identifier",
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
     *     property="name",
     *     description="AR section room text",
     *     type="string",
     *     example="Section cost item name",
     * ),
     * @OA\Property(
     *     property="flooring_type_id",
     *     description="Identifier of flooring type",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="flooring_subtype_id",
     *     description="Identifier of flooring subtype",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_length",
     *     description="Dimensions length",
     *     type="number",
     *     format="float",
     *     example=1.72,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_width",
     *     description="Dimensions width",
     *     type="number",
     *     format="float",
     *     example=0.95,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_height",
     *     description="Dimensions height",
     *     type="number",
     *     format="float",
     *     example=2.50,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_affected_length",
     *     description="Dimensions affected length",
     *     type="number",
     *     format="float",
     *     example=1.1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_affected_width",
     *     description="Dimensions affected width",
     *     type="number",
     *     format="float",
     *     example=0.5,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="underlay_required",
     *     description="Indicates whether underlay is required or not",
     *     type="boolean",
     *     default=false,
     *     example=true,
     * ),
     * @OA\Property(
     *     property="underlay_type_id",
     *     description="Identifier of underaly type",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="underlay_type_note",
     *     description="Note for underlay type",
     *     type="string",
     *     example="Wooden or cement underlay",
     * ),
     * @OA\Property(
     *     property="dimensions_underlay_length",
     *     description="Dimensions underlay length",
     *     type="number",
     *     format="float",
     *     example=2.02,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="dimensions_underlay_width",
     *     description="Dimensions underlay width",
     *     type="number",
     *     format="float",
     *     example=3.14,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="trims_required",
     *     description="Indicates whether trim is required or not",
     *     type="boolean",
     *     default=false,
     *     example=true,
     * ),
     * @OA\Property(
     *     property="trim_type",
     *     description="Trim type",
     *     type="string",
     *     example="Choke trim",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="restorable",
     *     description="Indicates whether room is retorable or not",
     *     type="boolean",
     *     default=false,
     *     example=true,
     * ),
     * @OA\Property(
     *     property="non_restorable_reason_id",
     *     description="Identifier of non restorable reason",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="carpet_type_id",
     *     description="Identifier of carpet type",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="carpet_construction_type_id",
     *     description="Identifier of carpet construction type",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="carpet_age_id",
     *     description="Identifier of carpet age",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="carpet_face_fibre_id",
     *     description="Identifier of carpet face fibre",
     *     type="integer",
     *     example=1,
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
        'created_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
        'dimensions_length'          => 'float',
        'dimensions_width'           => 'float',
        'dimensions_height'          => 'float',
        'dimensions_affected_length' => 'float',
        'dimensions_affected_width'  => 'float',
        'dimensions_underlay_length' => 'float',
        'dimensions_underlay_width'  => 'float',
    ];

    /**
     * Relationship with flooring_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flooringType(): BelongsTo
    {
        return $this->belongsTo(FlooringType::class);
    }

    /**
     * Relationship with flooring_subtypes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flooringSubtype(): BelongsTo
    {
        return $this->belongsTo(FlooringSubtype::class);
    }

    /**
     * Relationship with underlay_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function underlayType(): BelongsTo
    {
        return $this->belongsTo(UnderlayType::class);
    }

    /**
     * Relationship with non_restorable_reasons table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nonRestorableReason(): BelongsTo
    {
        return $this->belongsTo(NonRestorableReason::class);
    }

    /**
     * Relationship with carpet_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carpetType(): BelongsTo
    {
        return $this->belongsTo(CarpetType::class);
    }

    /**
     * Relationship with carpet_construction_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carpetConstructionType(): BelongsTo
    {
        return $this->belongsTo(CarpetConstructionType::class);
    }

    /**
     * Relationship with carpet_ages table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carpetAge(): BelongsTo
    {
        return $this->belongsTo(CarpetAge::class);
    }

    /**
     * Relationship with carpet_face_fibres table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carpetFaceFibre(): BelongsTo
    {
        return $this->belongsTo(CarpetFaceFibre::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): ?PositionableMapping
    {
        return null;
    }
}
