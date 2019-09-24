<?php

namespace Tests\API\AssessmentReports;

use Tests\API\ApiTestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportTestCase
 *
 * @package Tests\API\AssessmentReports
 */
abstract class AssessmentReportTestCase extends ApiTestCase
{
    use AssessmentReportFaker;
}
