<?php

namespace App\Components\Reporting\Interfaces;

use App\Components\Reporting\Models\Filters\ContactVolumeReportFilter;
use App\Components\Reporting\Models\VO\ContactVolumeReportData;

/**
 * Interface ContactVolumeReportServiceInterface
 *
 * @package App\Components\Reporting\Interfaces
 */
interface ContactVolumeReportServiceInterface
{
    /**
     * @param ContactVolumeReportFilter $filter Filter for report.
     */
    public function setFilter(ContactVolumeReportFilter $filter);

    /**
     * Returns report data.
     *
     * @return ContactVolumeReportData Report data.
     */
    public function getReportData(): ContactVolumeReportData;
}
