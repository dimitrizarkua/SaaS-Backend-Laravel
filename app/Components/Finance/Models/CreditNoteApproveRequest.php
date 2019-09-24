<?php

namespace App\Components\Finance\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class CreditNoteApproveRequest
 *
 * @property int             $requester_id
 * @property int             $approver_id
 * @property int             $credit_note_id
 * @property Carbon|null     $approved_at
 *
 * @property-read User       $requester
 * @property-read User       $approver
 * @property-read CreditNote $creditNote
 *
 * @OA\Schema(
 *     required={"requester_id","approver_id","credit_note_id"}
 * )
 */
class CreditNoteApproveRequest extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'credit_note_approve_requests';
    protected $fillable   = ['requester_id', 'approver_id', 'credit_note_id', 'approved_at'];
    protected $primaryKey = ['requester_id', 'approver_id', 'credit_note_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }
}
