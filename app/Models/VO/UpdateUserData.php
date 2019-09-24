<?php

namespace App\Models\VO;

use App\Core\JsonModel;

/**
 * Class UpdateUserData
 *
 * @package App\Models\VO
 */
class UpdateUserData extends JsonModel
{
    /**
     * @var string|null
     */
    public $first_name;

    /**
     * @var string|null
     */
    public $last_name;

    /**
     * @var float|null
     */
    public $purchase_order_approve_limit;

    /**
     * @var float|null
     */
    public $credit_note_approval_limit;

    /**
     * @var float|null
     */
    public $invoice_approve_limit;

    /**
     * @var float|null
     */
    public $working_hours_per_week;

    /**
     * @var integer|null
     */
    public $primary_location_id;

    /**
     * @var array|null
     */
    public $locations;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var integer|null
     */
    public $contact_id;

    /**
     * UpdateUserData constructor.
     *
     * @param array $properties Optional properties to be set to current instance.
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(array $properties = [])
    {
        $hidden       = array_diff_key(get_class_vars(static::class), $properties);
        $this->hidden = array_merge(array_keys($hidden), $this->hidden);
        parent::__construct($properties);
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        $locations       = $this->locations ?? [];
        $primaryLocation = $this->primary_location_id;
        $locations[]     = $primaryLocation;
        $locations       = array_unique(array_filter($locations));

        return array_map(function ($location) use ($primaryLocation) {
            return [$location, $location === $primaryLocation];
        }, $locations);
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();
        unset($result['primary_location_id'], $result['locations'], $result['password']);

        return $result;
    }
}
