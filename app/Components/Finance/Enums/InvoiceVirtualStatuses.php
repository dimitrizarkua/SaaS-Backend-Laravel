<?php

namespace App\Components\Finance\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class InvoiceVirtualStatuses
 *
 * @package App\Components\Finance\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Invoice virtual status",
 *     enum={"draft","overdue","unpaid","paid","pending_approval"},
 * )
 */
class InvoiceVirtualStatuses extends Enum
{
    public const DRAFT            = 'draft';
    public const OVERDUE          = 'overdue';
    public const UNPAID           = 'unpaid';
    public const PAID             = 'paid';
    public const PENDING_APPROVAL = 'pending_approval';

    protected static $values = [
        'DRAFT'            => self::DRAFT,
        'OVERDUE'          => self::OVERDUE,
        'UNPAID'           => self::UNPAID,
        'PAID'             => self::PAID,
        'PENDING_APPROVAL' => self::PENDING_APPROVAL,
    ];
}
