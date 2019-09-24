<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Events\CreditNoteItemCreated;
use App\Components\Finance\Events\CreditNoteItemDeleted;
use App\Components\Finance\Events\CreditNoteItemUpdated;
use App\Components\Models\PositionableMapping;
use App\Models\ApiRequestFillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteItem
 *
 * @property int             $credit_note_id
 * @property Carbon          $created_at
 * @property-read CreditNote $creditNote
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","credit_note_id","gs_code_id","description","quantity","unit_cost","gl_account_id","tax_rate_id"}
 * )
 *
 * @mixin \Eloquent
 */
class CreditNoteItem extends FinancialEntityItem
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="credit_note_id",
     *     description="Identifier of a credit note",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="gs_code_id",
     *     description="Identifier of a GS code",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Credit note item description",
     *     type="string",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="quantity",
     *     description="The number of units in item",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="unit_cost",
     *     description="Single unit cost",
     *     type="number",
     *     format="float",
     *     example=1.5,
     * ),
     * @OA\Property(
     *     property="gl_account_id",
     *     description="Identifier of a GL account",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="tax_rate_id",
     *     description="Identifier of a tax rate",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="position",
     *     description="Credit note item position",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'unit_cost'  => 'float',
    ];

    /**
     * Associated credit note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    /**
     * @inheritDoc
     */
    public function getAmountForOneUnit(): float
    {
        return $this->unit_cost;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (CreditNoteItem $creditNoteItem) {
            event(new CreditNoteItemCreated($creditNoteItem->creditNote));
        });

        static::updated(function (CreditNoteItem $creditNoteItem) {
            event(new CreditNoteItemUpdated($creditNoteItem->creditNote));
        });

        static::deleted(function (CreditNoteItem $creditNoteItem) {
            event(new CreditNoteItemDeleted($creditNoteItem->creditNote));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionableMapping(): PositionableMapping
    {
        return new PositionableMapping($this->creditNote()->getForeignKey());
    }

    /**
     * Query to include total amount of credit notes items with tax.
     *
     * @param string $columnName
     *
     * @return string
     */
    public static function totalAmountIncludeTaxSubQueryString(string $columnName): string
    {
        return sprintf(
            '(SELECT SUM(sub.unit_cost * (1 + tax_rates.rate) * sub.quantity) AS %s
            FROM (
                SELECT id, unit_cost, quantity, tax_rate_id
                FROM credit_note_items
                WHERE credit_note_items.credit_note_id = credit_notes.id
            ) AS sub
            LEFT JOIN tax_rates
            ON sub.tax_rate_id = tax_rates.id)',
            $columnName
        );
    }
}
