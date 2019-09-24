<?php

namespace App\Components\Finance\Enums;

/**
 * Class PurchaseOrderCountersCacheKeys
 *
 * @package App\Components\Finance\Enums
 *
 */
class PurchaseOrderCountersCacheKeys
{
    /**
     * Cache keys format purchase_order:{key_name}:{cache_type}:{location_id}
     */
    public const COUNTER_KEY_FORMAT = 'purchase_order:counter:%s:%d';
    public const AMOUNT_KEY_FORMAT  = 'purchase_order:amount:%s:%d';

    /**
     * Types of allowed cache type
     */
    public const CACHE_TYPE_DRAFT    = 'draft';
    public const CACHE_TYPE_PENDING  = 'pending';
    public const CACHE_TYPE_APPROVED = 'approved';

    public const TAG_KEY = 'purchase_orders_info';

    public const TTL_IN_MINUTES = 7 * 24 * 60;
}
