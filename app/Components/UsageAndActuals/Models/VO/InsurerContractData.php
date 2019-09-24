<?php

namespace App\Components\UsageAndActuals\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class InsurerContractData
 *
 * @package App\Components\UsageAndActuals\Models\VO
 */
class InsurerContractData extends JsonModel
{
    /**
     * @var int
     */
    public $contact_id;
    /**
     * @var string
     */
    public $contract_number;
    /**
     * @var string|null
     */
    public $description;
    /**
     * @var \Illuminate\Support\Carbon
     */
    public $effect_date;
    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $termination_date;

    /**
     * @param string|null $effect_date
     *
     * @return \App\Components\UsageAndActuals\Models\VO\InsurerContractData
     */
    public function setEffectDate(?string $effect_date): self
    {
        if (null !== $effect_date) {
            $this->effect_date = new Carbon($effect_date);
        }

        return $this;
    }

    /**
     * @param string|null $termination_date
     *
     * @return \App\Components\UsageAndActuals\Models\VO\InsurerContractData
     */
    public function setTerminationDate(?string $termination_date): self
    {
        if (null !== $termination_date) {
            $this->termination_date = new Carbon($termination_date);
        }

        return $this;
    }

    /**
     * Returns the insurer working under the contract.
     *
     * @return int|null
     */
    public function getContactId(): ?int
    {
        return $this->contact_id;
    }

    /**
     * Contract number.
     *
     * @return string
     */
    public function getContractNumber(): string
    {
        return $this->contract_number;
    }

    /**
     * Description of insurer contract.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Date when contract activated.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getEffectDate(): ?Carbon
    {
        return $this->effect_date;
    }

    /**
     * Date when contract terminated.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getTerminationDate(): ?Carbon
    {
        return $this->termination_date;
    }

    /**
     * Serializes object to array.
     *
     * @return array representation of object
     */
    public function toArray(): array
    {
        $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result     = [];
        foreach ($properties as $property) {
            $propertyName = $property->name;
            if (is_null($this->$propertyName)) {
                continue;
            }
            if (!empty($this->hidden) && in_array($propertyName, $this->hidden)) {
                continue;
            }
            $result[$propertyName] = $this->$propertyName;
        }

        return $result;
    }
}
