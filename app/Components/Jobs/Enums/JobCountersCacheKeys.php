<?php

namespace App\Components\Jobs\Enums;

/**
 * Class JobCountersCacheKeys
 *
 * @package App\Components\Jobs\Enums
 *
 */
class JobCountersCacheKeys
{
    public const INBOX_KEY         = 'users:jobs:counters:inbox';
    public const MINE_KEY_PATTERN  = 'users:%d:jobs:counters:mine';
    public const TEAMS_KEY_PATTERN = 'teams:%d:jobs:counters';

    public const TAG_KEY = 'job_counters';

    public const TTL_IN_MINUTES = 7 * 24 * 60;
}
