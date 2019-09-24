<?php

namespace App\Components\Tags\Mappers;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Tags\Enums\TagTypes;

/**
 * Class TagTypesMapper
 *
 * @package App\Components\Tags\Mappers
 */
class TagTypesMapper
{
    /** @var array */
    protected static $mapping = [
        TagTypes::JOB            => Job::class,
        TagTypes::CONTACT        => Contact::class,
        TagTypes::PURCHASE_ORDER => PurchaseOrder::class,
    ];

    /**
     * Returns whole mapping array or class name which match provided type.
     *
     * @param string|null $type Tag type.
     *
     * @return array|string
     */
    public static function getMapping($type = null)
    {
        if ($type) {
            return static::$mapping[$type] ?? [];
        } else {
            return static::$mapping;
        }
    }
}
