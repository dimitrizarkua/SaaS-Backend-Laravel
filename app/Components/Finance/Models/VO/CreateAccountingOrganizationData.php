<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class CreateAccountingOrganizationData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreateAccountingOrganizationData extends JsonModel
{
    /**
     * @var int
     */
    public $contact_id;
    /**
     * @var int|null
     */
    public $lock_day_of_month;
    /**
     * @var null|string
     */
    public $cc_payments_api_key;
    /**
     * @var int|null
     */
    public $location_id;

    /**
     * @return int|null
     */
    public function getLocationId(): ?int
    {
        return $this->location_id;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();
        unset($result['location_id']);

        return $result;
    }
}
