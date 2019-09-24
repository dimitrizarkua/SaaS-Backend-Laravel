<?php

namespace App\Components\Finance\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class InvoiceApproveRequest
 *
 * @property int          $invoice_id
 * @property int          $requester_id
 * @property int          $approver_id
 * @property mixed|null   $approved_at
 * @property-read User    $approver
 * @property-read Invoice $invoice
 * @property-read User    $requester
 *
 * @method static Builder|InvoiceApproveRequest newModelQuery()
 * @method static Builder|InvoiceApproveRequest newQuery()
 * @method static Builder|InvoiceApproveRequest query()
 * @method static Builder|InvoiceApproveRequest whereApprovedAt($value)
 * @method static Builder|InvoiceApproveRequest whereApproverId($value)
 * @method static Builder|InvoiceApproveRequest whereInvoiceId($value)
 * @method static Builder|InvoiceApproveRequest whereRequesterId($value)
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 */
class InvoiceApproveRequest extends Model
{
    use HasCompositePrimaryKey;
    public $incrementing = false;
    public $timestamps   = false;

    public $primaryKey = ['invoice_id', 'requester_id', 'approver_id'];

    /**
     * @var array
     */
    protected $fillable = ['*'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'approved_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
