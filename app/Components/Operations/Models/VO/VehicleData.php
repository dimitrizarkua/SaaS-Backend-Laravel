<?php

namespace App\Components\Operations\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class VehicleData
 *
 * @package App\Components\Operations\Models\VO
 */
class VehicleData extends JsonModel
{
    /**
     * @var integer
     */
    public $location_id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $make;

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $registration;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $rent_starts_at;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $rent_ends_at;

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->location_id;
    }

    /**
     * @param int $locationId
     */
    public function setLocationId(int $locationId): void
    {
        $this->location_id = $locationId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->make;
    }

    /**
     * @param string $make
     */
    public function setMake(string $make): void
    {
        $this->make = $make;
    }

    /**
     * @return string
     */
    public function getRegistration(): string
    {
        return $this->registration;
    }

    /**
     * @param string $registration
     */
    public function setRegistration(string $registration): void
    {
        $this->registration = $registration;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getRentStartsAt(): ?Carbon
    {
        return $this->rent_starts_at;
    }

    /**
     * @param string|null $rentStartsAt
     *
     * @return self
     */
    public function setRentStartsAt(?string $rentStartsAt): self
    {
        if (null !== $rentStartsAt) {
            $this->rent_starts_at = new Carbon($rentStartsAt);
        } else {
            $this->rent_starts_at = null;
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getRentEndsAt(): ?\Illuminate\Support\Carbon
    {
        return $this->rent_ends_at;
    }

    /**
     * @param string|null $rentEndsAt
     *
     * @return self
     */
    public function setRentEndsAt(?string $rentEndsAt): self
    {
        if (null !== $rentEndsAt) {
            $this->rent_ends_at = new Carbon($rentEndsAt);
        } else {
            $this->rent_ends_at = null;
        }

        return $this;
    }
}
