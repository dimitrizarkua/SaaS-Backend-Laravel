<?php

namespace App\Components\Jobs\Models\VO;

use App\Core\JsonModel;

/**
 * Class RecurringJobCreationData
 *
 * @package App\Components\Jobs\Models\VO
 */
class RecurringJobCreationData extends JsonModel
{
    /**
     * @var string
     */
    public $recurrence_rule;

    /**
     * @var int
     */
    public $insurer_id;

    /**
     * @var int
     */
    public $job_service_id;

    /**
     * @var int
     */
    public $site_address_id;

    /**
     * @var int
     */
    public $owner_location_id;

    /**
     * @var string
     */
    public $description;

    /**
     * @return string
     */
    public function getRecurrenceRule(): string
    {
        return $this->recurrence_rule;
    }

    /**
     * @param string $recurrence_rule
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setRecurrenceRule(string $recurrence_rule): self
    {
        $this->recurrence_rule = $recurrence_rule;

        return $this;
    }

    /**
     * @return int
     */
    public function getInsurerId(): int
    {
        return $this->insurer_id;
    }

    /**
     * @param int $insurer_id
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setInsurerId(int $insurer_id): self
    {
        $this->insurer_id = $insurer_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getJobServiceId(): int
    {
        return $this->job_service_id;
    }

    /**
     * @param int $job_service_id
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setJobServiceId(int $job_service_id): self
    {
        $this->job_service_id = $job_service_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSiteAddressId(): int
    {
        return $this->site_address_id;
    }

    /**
     * @param int $site_address_id
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setSiteAddressId(int $site_address_id): self
    {
        $this->site_address_id = $site_address_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerLocationId(): int
    {
        return $this->owner_location_id;
    }

    /**
     * @param int $owner_location_id
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setOwnerLocationId(int $owner_location_id): self
    {
        $this->owner_location_id = $owner_location_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
