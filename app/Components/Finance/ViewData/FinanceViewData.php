<?php

namespace App\Components\Finance\ViewData;

use App\Components\Addresses\Models\Address;
use App\Components\Finance\Models\FinancialEntity;

/**
 * Class FinanceViewData
 *
 * @package App\Components\Finance\ViewData
 */
class FinanceViewData
{
    /**
     * @var FinancialEntity
     */
    protected $entity;

    /**
     * FinanceViewData constructor.
     *
     * @param $entity
     */
    public function __construct(FinancialEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Returns array representation of an address.
     *
     * @param Address|string $address Address to be formatted.
     *
     * @return array
     */
    protected function formatAddress($address = null): ?array
    {
        if (null === $address) {
            return null;
        }

        if (is_string($address)) {
            return [
                'addressLine' => $address,
            ];
        }

        if (!$address->suburb_id) {
            return [
                'addressLine' => $address->address_line_1,
            ];
        }

        return [
            'addressLine'    => $address->address_line_1,
            'suburbAndState' => sprintf(
                '%s %s %s',
                $address->suburb->name,
                $address->suburb->state->code,
                $address->suburb->postcode
            ),
            'country'        => $address->suburb->state->country->name,
        ];
    }

    /**
     * @return array
     */
    protected function getJobInfo(): array
    {
        $jobInfo = [];
        if ($this->entity->job_id) {
            $customer = $this->getCustomerInfo();

            $siteAddress = null;
            if ($this->entity->job->siteAddress) {
                $siteAddress = $this->formatAddress($this->entity->job->siteAddress);
            }

            $jobInfo = [
                'number'        => $this->entity->job->id,
                'reference'     => $this->entity->job->reference_number,
                'claim'         => $this->entity->job->claim_number,
                'customer'      => $customer,
                'site_address'  => $siteAddress,
                'loss_adjustor' => '',
            ];
        }

        return $jobInfo;
    }

    /**
     * @return array|null
     */
    protected function getCustomerInfo(): ?array
    {
        $customer = null;

        if ($this->entity->job->insurer) {
            $address = $this->entity->job->insurer->getMailingAddress();
            if (null !== $address) {
                $customer = [
                    'name'    => $this->entity->job->insurer->getContactName(),
                    'address' => $this->formatAddress($address),
                ];
            }
        }

        return $customer;
    }

    /**
     * @param string $abn
     *
     * @return string
     */
    protected function formatAbn(string $abn = null): ?string
    {
        if (null === $abn) {
            return null;
        }

        if (!preg_match('/\d{2}\s(\d{3}\s){2}\d{3}/', $abn)) {
            $abn = preg_replace('[^0-9]', '', $abn);

            return substr($abn, 0, 2) . ' '
                . substr($abn, 2, 3) . ' '
                . substr($abn, 5, 3) . ' '
                . substr($abn, 8, 3);
        }

        return $abn;
    }

    /**
     * @param string $phone
     *
     * @return string
     */
    protected function formatPhone(string $phone): string
    {
        if (!preg_match('/\d{4}\s\d{3}\s\d{3}/', $phone)) {
            $phone = preg_replace('[^0-9]', '', $phone);

            return substr($phone, 0, 4) . ' '
                . substr($phone, 5, 3) . ' '
                . substr($phone, 5, 3);
        }

        return $phone;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function formatUrl(string $url = null): ?string
    {
        if (null === $url) {
            return $url;
        }

        return preg_replace('/http\:\/\//', '', $url);
    }
}
