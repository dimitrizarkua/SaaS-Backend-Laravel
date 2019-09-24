<?php

namespace App\Components\Finance\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class PurchaseOrderVirtualStatuses
 *
 * @package App\Components\Finance\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Financial entity status",
 *     enum={"draft","approved","pending_approval"},
 * )
 */
class PurchaseOrderVirtualStatuses extends Enum
{
    public const DRAFT            = 'draft';
    public const APPROVED         = 'approved';
    public const PENDING_APPROVAL = 'pending_approval';

    protected static $values = [
        'DRAFT'            => self::DRAFT,
        'APPROVED'         => self::APPROVED,
        'PENDING_APPROVAL' => self::PENDING_APPROVAL,
    ];
}
