<?php

namespace App\Components\AssessmentReports;

use App\Components\Addresses\Models\Address;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Contracts\ViewDataInterface;

/**
 * Class AssessmentReportPrintVersion
 *
 * @package App\Components\AssessmentReports
 */
class AssessmentReportPrintVersion implements ViewDataInterface
{
    /** @var AssessmentReport */
    protected $assessmentReport;

    /**
     * AssessmentReportPrintVersion constructor.
     *
     * @param AssessmentReport $assessmentReport
     */
    public function __construct(AssessmentReport $assessmentReport)
    {
        $this->assessmentReport = $assessmentReport;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $this->assessmentReport->sections
            ->where('type', AssessmentReportSectionTypes::COSTS)
            ->each(function (AssessmentReportSection $section) {
                $section->append('cost_summary');
                $section->costItems->each(function (AssessmentReportSectionCostItem $costItem) {
                    $costItem->append('total_amount');
                });
            });

        return [
            'assessmentReport' => $this->assessmentReport->toArray(),
            'date'             => $this->assessmentReport->date->format('d F y'),
            'job'              => $this->getJobInfo(),
            'customer'         => $this->getCustomerInfo(),
        ];
    }

    /**
     * Returns string representation of an address.
     *
     * @param Address|string|null $address Address to be formatted.
     *
     * @return string|null
     */
    protected function formatAddress($address): ?string
    {
        if (null === $address || is_string($address)) {
            return $address;
        }

        if ($address instanceof Address) {
            if (!$address->suburb_id) {
                return $address->address_line_1;
            }

            return sprintf(
                '%s, %s, %s, %s',
                $address->address_line_1,
                strtoupper($address->suburb->name),
                strtoupper($address->suburb->state->code),
                strtoupper($address->suburb->postcode)
            );
        }

        return null;
    }

    /**
     * Returns array representation of an assessment report job.
     *
     * @return array
     */
    protected function getJobInfo(): array
    {
        $siteAddress = null;
        if ($this->assessmentReport->job->siteAddress) {
            $siteAddress = $this->formatAddress($this->assessmentReport->job->siteAddress);
        }

        return [
            'number'       => $this->assessmentReport->job->id,
            'reference'    => $this->assessmentReport->job->reference_number,
            'claim'        => $this->assessmentReport->job->claim_number,
            'site_address' => $siteAddress,
        ];
    }

    /**
     * Returns array representation of an assessment report customer.
     *
     * @return array|null
     */
    protected function getCustomerInfo(): ?array
    {
        $customer = null;

        if ($this->assessmentReport->job->insurer) {
            $address = $this->assessmentReport->job->insurer->getMailingAddress();
            if (null !== $address) {
                $customer = [
                    'name'    => $this->assessmentReport->job->insurer->getContactName(),
                    'address' => $this->formatAddress($address),
                ];
            }
        }

        return $customer;
    }
}
