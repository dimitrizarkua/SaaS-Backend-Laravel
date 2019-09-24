<?php

namespace App\Components\Notifications\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class NotificationSettingTypes
 *
 * @package App\Components\Notifications\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Notifications setting types",
 *     enum={""},
 * )
 */
class NotificationSettingTypes extends Enum
{
    public const JOB_CREATED             = 'job.created';
    public const JOB_UPDATED             = 'job.updated';
    public const JOB_DELETED             = 'job.deleted';
    public const JOB_ASSIGNED_TO_ME      = 'job.assigned_to_me';
    public const JOB_ASSIGNED_TO_SOMEONE = 'job.assigned_to_someone';

    public const NOTE_ADDED_TO_UNASSIGNED_JOB       = 'job.note_added_to_unassigned_job';
    public const NOTE_ADDED_TO_MY_JOB               = 'job.note_added_to_my_job';
    public const NOTE_ADDED_TO_JOB_OWNED_BY_SOMEONE = 'job.note_added_to_job_owned_by_someone';

    public const MESSAGE_ADDED_TO_UNASSIGNED_JOB       = 'job.message_added_to_unassigned_job';
    public const MESSAGE_ADDED_TO_MY_JOB               = 'job.message_added_to_my_job';
    public const MESSAGE_ADDED_TO_JOB_OWNED_BY_SOMEONE = 'job.message_added_to_job_owned_by_someone';

    public const USER_MENTIONED = 'user_mentioned';
}
