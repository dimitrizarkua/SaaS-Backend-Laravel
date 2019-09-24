<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\FinancialEntityStatusInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class CreditNoteStatus
 *
 * @property int             $id
 * @property int             $credit_note_id
 * @property int|null        $user_id
 * @property string          $status
 * @property Carbon          $created_at
 *
 * @property-read CreditNote $creditNote
 * @property-read User       $user
 *
 * @OA\Schema(
 *     required={"id","credit_note_id","status"}
 * )
 */
class CreditNoteStatus extends Model implements FinancialEntityStatusInterface
{
    const UPDATED_AT = null;

    /**
     * The map of possible changes of statuses.
     *
     * @var array
     */
    private $statusesMap = [
        FinancialEntityStatuses::DRAFT => [
            FinancialEntityStatuses::APPROVED,
        ],
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
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
     * A user who put a credit note in this state.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @inheritDoc
     */
    public function canBeChangedTo(string $newStatus): bool
    {
        if ($newStatus === $this->status) {
            return false;
        }

        return array_key_exists($this->status, $this->statusesMap)
            && in_array($newStatus, $this->statusesMap[$this->status], true);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
