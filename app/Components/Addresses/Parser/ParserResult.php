<?php

namespace App\Components\Addresses\Parser;

/**
 * Class ParserResult
 *
 * @package App\Components\Addresses\Parser
 */
class ParserResult
{
    /**
     * @var null|string
     */
    private $addressLine1;
    /**
     * @var null|string
     */
    private $suburb;
    /**
     * @var null|int
     */
    private $postCode;
    /**
     * @var null|string
     */
    private $country;
    /**
     * @var null|string
     */
    private $stateCode;

    /**
     * ParserResult constructor.
     *
     * @param null|string $addressLine1
     * @param null|string $addressLine2
     * @param null|string $suburb
     * @param int|null    $postCode
     * @param null|string $country
     */
    public function __construct(
        string $addressLine1 = null,
        string $suburb = null,
        string $stateCode = null,
        int $postCode = null,
        string $country = null
    ) {
        $this->addressLine1 = $addressLine1;
        $this->suburb       = $suburb;
        $this->stateCode    = $stateCode;
        $this->postCode     = $postCode;
        $this->country      = $country;
    }

    /**
     * @return null|string
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * @return null|string
     */
    public function getSuburb(): ?string
    {
        return $this->suburb;
    }

    /**
     * @return int|null
     */
    public function getPostCode(): ?int
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getStateCode(): ?string
    {
        return $this->stateCode;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }
}
