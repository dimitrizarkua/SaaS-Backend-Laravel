<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\FinancialEntityStatusInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceStatus
 *
 * @property int            $id
 * @property int            $invoice_id
 * @property int|null       $user_id
 * @property string         $status
 * @property mixed|null     $created_at
 * @property-read Invoice   $invoice
 * @property-read User|null $user
 *
 * @method static Builder|InvoiceStatus newModelQuery()
 * @method static Builder|InvoiceStatus newQuery()
 * @method static Builder|InvoiceStatus query()
 * @method static Builder|InvoiceStatus whereCreatedAt($value)
 * @method static Builder|InvoiceStatus whereId($value)
 * @method static Builder|InvoiceStatus whereInvoiceId($value)
 * @method static Builder|InvoiceStatus whereStatus($value)
 * @method static Builder|InvoiceStatus whereUserId($value)
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "invoice_id",
 *          "user_id",
 *          "status",
 *          "created_at",
 *     },
 * )
 */
class InvoiceStatus extends Model implements FinancialEntityStatusInterface
{
    public const UPDATED_AT = null;
    protected $guarded = ['id'];

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="invoice_id",
     *    description="Invoice identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="user_id",
     *    description="User identifier who updated the status",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="status",
     *    description="Status",
     *    type="string",
     *    enum={"draft", "approved"}
     * ),
     */

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
    public function user()
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
