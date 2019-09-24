<?php

namespace App\Components\Reporting\Interfaces;

use App\Components\Reporting\Models\VO\GlAccountTrialReportFilterData;
use Illuminate\Support\Collection;

/**
 * Interface ReportingGLAccountServiceInterface
 *
 * @package App\Components\Reporting\Interfaces
 */
interface ReportingGLAccountServiceInterface
{
    /**
     * Returns data collection for trial report for location.
     *
     * @param  GlAccountTrialReportFilterData $filter Filter instance.
     *
     * @throws \Throwable
     * @return Collection
     */
    public function getGlAccountTrialReport(GlAccountTrialReportFilterData $filter): Collection;
}
