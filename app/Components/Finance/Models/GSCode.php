<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * Class GSCode
 *
 * @package App\Components\Finance\Models
 *
 * @property int    $id
 * @property string $name
 * @property string $description
 * @property bool   $is_buy
 * @property bool   $is_sell
 *
 * @method static Builder|GSCode whereId($value)
 * @method static Builder|GSCode whereName($value)
 * @method static Builder|GSCode whereDescription($value)
 * @method static Builder|GSCode whereIsBuy($value)
 * @method static Builder|GSCode whereIsSell($value)
 *
 * @OA\Schema(
 *     required={"id","name"}
 * )
 *
 * @mixin \Eloquent
 */
class GSCode extends Model
{
    use ApiRequestFillable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'gs_codes';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @OA\Property(
     *    property="id",
     *    description="GS code identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="name",
     *    description="GS code name",
     *    type="string",
     *    example="GS code name"
     * ),
     * @OA\Property(
     *    property="description",
     *    description="GS code description",
     *    type="string",
     *    example="GS code description"
     * )
     * @OA\Property(
     *    property="is_buy",
     *    description="Indicates if GS code is for item buy",
     *    type="boolean",
     *    example="false"
     * )
     * @OA\Property(
     *    property="is_sell",
     *    description="Indicates if GS code is for item sell",
     *    type="boolean",
     *    example="false"
     * )
     */

    /**
     * Defines relation for invoice items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'gs_code_id');
    }

    /**
     * Defines relation for purchase order items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'gs_code_id');
    }

    /**
     * Defines relation for credit notes items.
     *
     * @return void
     */
    public function creditNoteItems()
    {
        // TODO ADD Credit note items
        //return $this->hasMany(CreditNoteItem::class);
        return;
    }
}
