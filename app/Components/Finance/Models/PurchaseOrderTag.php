<?php

namespace App\Components\Finance\Models;

use App\Components\Tags\Models\Tag;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PurchaseOrderTag
 *
 * @package App\Components\Finance\Models
 *
 * @mixin \Eloquent
 * @property int                $purchase_order_id
 * @property int                $tag_id
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read Tag           $tag
 */
class PurchaseOrderTag extends Model
{
    use HasCompositePrimaryKey;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_order_tag';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['purchase_order_id', 'tag_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['purchase_order_id', 'tag_id'];

    /**
     * Relationship with purchase_orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Relationship with tags table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
