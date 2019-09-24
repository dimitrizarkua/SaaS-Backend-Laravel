<?php

namespace App\Components\AssessmentReports\Models;

use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostItem
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property integer                           $assessment_report_costing_stage_id
 * @property integer                           $gs_code_id
 * @property string                            $description
 * @property integer                           $quantity
 * @property float                             $unit_cost
 * @property float                             $discount
 * @property float                             $markup
 * @property integer                           $tax_rate_id
 * @property-read AssessmentReportCostingStage $costingStage
 * @property-read GSCode                       $gsCode
 * @property-read TaxRate                      $taxRate
 *
 * @method static Builder|AssessmentReportCostItem whereAssessmentReportCostingStageId($value)
 * @method static Builder|AssessmentReportCostItem whereAssessmentReportId($value)
 * @method static Builder|AssessmentReportCostItem whereGSCodeId($value)
 * @method static Builder|AssessmentReportCostItem whereDescription($value)
 * @method static Builder|AssessmentReportCostItem whereQuantity($value)
 * @method static Builder|AssessmentReportCostItem whereUnitCost($value)
 * @method static Builder|AssessmentReportCostItem whereDiscount($value)
 * @method static Builder|AssessmentReportCostItem whereMarkup($value)
 * @method static Builder|AssessmentReportCostItem whereTaxRateId($value)
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "assessment_report_costing_stage_id",
 *         "assessment_report_id",
 *         "gs_code_id",
 *         "position",
 *         "description",
 *         "quantity",
 *         "unit_cost",
 *         "discount",
 *         "markup",
 *         "tax_rate_id",
 *         "created_at",
 *         "updated_at"
 *     }
 * )
 */
class AssessmentReportCostItem extends AssessmentReportEntity
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="AR cost item identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="assessment_report_costing_stage_id",
     *     description="Identifier of AR costing stage",
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
     *     property="gs_code_id",
     *     description="Identifier of GS code",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="AR cost item position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="description",
     *     description="AR cost item text",
     *     type="string",
     *     example="Cost item description",
     * ),
     * @OA\Property(
     *     property="quantity",
     *     description="Used quantity",
     *     type="integer",
     *     example=2,
     * ),
     * @OA\Property(
     *     property="unit_cost",
     *     description="Cost per unit",
     *     type="number",
     *     format="float",
     *     example=500.00,
     * ),
     * @OA\Property(
     *     property="discount",
     *     description="Discount in percent",
     *     type="number",
     *     format="float",
     *     example=25.00,
     * ),
     * @OA\Property(
     *     property="markup",
     *     description="Markup in percent",
     *     type="number",
     *     format="float",
     *     example=30.00,
     * ),
     * @OA\Property(
     *     property="tax_rate_id",
     *     description="Identifier of tax rate",
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
        'unit_cost'  => 'float',
        'discount'   => 'float',
        'markup'     => 'float',
    ];

    /**
     * Relationship with assessment_report_costing_stages table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costingStage(): BelongsTo
    {
        return $this->belongsTo(AssessmentReportCostingStage::class, 'assessment_report_costing_stage_id');
    }

    /**
     * Relationship with gs_codes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gsCode(): BelongsTo
    {
        return $this->belongsTo(GSCode::class, 'gs_code_id');
    }

    /**
     * Relationship with tax_rates table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    /**
     * Returns cost item total amount.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->getAmountForOneUnit() * $this->quantity;
    }

    /**
     * Returns amount for the one unit.
     *
     * @return float
     */
    public function getAmountForOneUnit(): float
    {
        $difference         = $this->markup - $this->discount;
        $costWithDifference = $this->unit_cost * ($difference / 100);

        return $this->unit_cost + $costWithDifference;
    }

    /**
     * Returns tax for cost item.
     *
     * @return float
     */
    public function getItemTax(): float
    {
        return $this->getTotalAmount() * $this->taxRate->rate;
    }
}
