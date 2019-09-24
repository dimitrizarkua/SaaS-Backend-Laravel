<?php

namespace App\Enums;

use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTask;
use App\Components\Search\Models\UserAndTeam;
use App\Components\Tags\Models\Tag;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\Material;
use App\Models\User;
use vijinho\Enums\Enum;
use OpenApi\Annotations as OA;

/**
 * Class ElasticIndexableModels
 *
 * @package App\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Financial entity status",
 *     enum={
 *         "purchase_orders",
 *         "tags",
 *         "suburbs",
 *         "contacts",
 *         "users",
 *         "users_and_teams",
 *         "jobs",
 *         "job_tasks",
 *         "credit_notes",
 *         "invoices",
 *         "equipment",
 *         "materials",
 *     }
 * )
 */
class ElasticIndexableModels extends Enum
{
    public const SUBURBS         = 'suburbs';
    public const TAGS            = 'tags';
    public const CONTACTS        = 'contacts';
    public const USERS           = 'users';
    public const USERS_AND_TEAMS = 'users_and_teams';
    public const JOBS            = 'jobs';
    public const JOB_TASKS       = 'job_tasks';
    public const PURCHASE_ORDERS = 'purchase_orders';
    public const INVOICES        = 'invoices';
    public const EQUIPMENT       = 'equipment';
    public const MATERIALS       = 'materials';
    public const CREDIT_NOTES    = 'credit_notes';

    protected static $values = [
        self::SUBURBS         => Suburb::class,
        self::TAGS            => Tag::class,
        self::CONTACTS        => Contact::class,
        self::USERS           => User::class,
        self::USERS_AND_TEAMS => UserAndTeam::class,
        self::JOBS            => Job::class,
        self::JOB_TASKS       => JobTask::class,
        self::PURCHASE_ORDERS => PurchaseOrder::class,
        self::INVOICES        => Invoice::class,
        self::EQUIPMENT       => Equipment::class,
        self::MATERIALS       => Material::class,
        self::CREDIT_NOTES    => CreditNote::class,
    ];
}
