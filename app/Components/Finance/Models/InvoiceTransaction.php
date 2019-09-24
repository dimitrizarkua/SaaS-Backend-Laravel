<?php

namespace App\Components\Finance\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceTransaction
 *
 * @property int   $transaction_id
 * @property int   $invoice_id
 * @property float $amount
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 */
class InvoiceTransaction extends Model
{
    use HasCompositePrimaryKey;

    public $fillable   = ['transaction_id', 'invoice_id'];
    public $primaryKey = ['transaction_id', 'invoice_id'];
    public $timestamps = false;

    protected $table = 'invoice_transaction';
}
