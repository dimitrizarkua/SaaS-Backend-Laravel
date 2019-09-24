<?php

namespace Tests\API\Jobs;

use Tests\API\ApiTestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobTestCase
 *
 * @package Tests\API\Jobs
 */
abstract class JobTestCase extends ApiTestCase
{
    use JobFaker;
}
