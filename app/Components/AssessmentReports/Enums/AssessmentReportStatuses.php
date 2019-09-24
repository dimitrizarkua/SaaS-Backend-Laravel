<?php

namespace App\Components\AssessmentReports\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class AssessmentReportStatuses
 *
 * @package App\Components\AssessmentReports\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Assessment report status",
 *     enum={"draft","pending_client_approval","cancelled","client_approved","client_cancelled"},
 * )
 */
class AssessmentReportStatuses extends Enum
{
    public const DRAFT                   = 'draft';
    public const PENDING_CLIENT_APPROVAL = 'pending_client_approval';
    public const CANCELLED               = 'cancelled';
    public const CLIENT_APPROVED         = 'client_approved';
    public const CLIENT_CANCELLED        = 'client_cancelled';

    protected static $values = [
        'DRAFT'                   => self::DRAFT,
        'PENDING_CLIENT_APPROVAL' => self::PENDING_CLIENT_APPROVAL,
        'CANCELLED'               => self::CANCELLED,
        'CLIENT_APPROVED'         => self::CLIENT_APPROVED,
        'CLIENT_CANCELLED'        => self::CLIENT_CANCELLED,
    ];

    /**
     * List of cancelled statuses.
     *
     * @var array
     */
    public static $cancelledStatuses = [
        self::CANCELLED,
        self::CLIENT_CANCELLED,
    ];
}
