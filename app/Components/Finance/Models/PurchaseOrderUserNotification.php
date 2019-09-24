<?php

namespace App\Components\Finance\Models;

use App\Components\Notifications\Models\UserNotification;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PurchaseOrderUserNotification
 *
 * @package App\Components\Finance\Models
 *
 * @mixin \Eloquent
 * @property int                   $user_notification_id
 * @property int                   $purchase_order_id
 * @property-read PurchaseOrder    $purchaseOrder
 * @property-read UserNotification $notification
 */
class PurchaseOrderUserNotification extends Model
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
    protected $table = 'purchase_order_user_notification';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['purchase_order_id', 'user_notification_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['purchase_order_id', 'user_notification_id'];

    /**
     * Relation with purchase_orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Relation with user_notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(
            UserNotification::class,
            'id',
            'user_notification_id'
        );
    }
}
