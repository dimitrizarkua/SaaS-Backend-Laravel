<?php

namespace App\Components\Contacts\Models;

/**
 * Class CompanyData
 *
 * @package App\Components\Contacts\Models
 */
class CompanyData extends ContactData
{
    /**
     * Legal company name.
     *
     * @var string
     */
    public $legal_name;

    /**
     * Trading company name.
     *
     * @var string|null
     */
    public $trading_name;

    /**
     * Australian Business Number.
     *
     * @var string
     */
    public $abn;

    /**
     * Website URL.
     *
     * @var string|null
     */
    public $website;

    /**
     * Default payment terms (in days).
     *
     * @var int
     */
    public $default_payment_terms_days;

    /**
     * @return string
     */
    public function getLegalName(): string
    {
        return $this->legal_name;
    }

    /**
     * @param string $legal_name
     *
     * @return self
     */
    public function setLegalName(string $legal_name): self
    {
        $this->legal_name = $legal_name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTradingName(): ?string
    {
        return $this->trading_name;
    }

    /**
     * @param null|string $trading_name
     *
     * @return self
     */
    public function setTradingName(?string $trading_name): self
    {
        $this->trading_name = $trading_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbn(): string
    {
        return $this->abn;
    }

    /**
     * @param string $abn
     *
     * @return self
     */
    public function setAbn(string $abn): self
    {
        $this->abn = $abn;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param null|string $website
     *
     * @return self
     */
    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultPaymentTermsDays(): int
    {
        return $this->default_payment_terms_days;
    }

    /**
     * @param int $default_payment_terms_days
     *
     * @return self
     */
    public function setDefaultPaymentTermsDays(int $default_payment_terms_days): self
    {
        $this->default_payment_terms_days = $default_payment_terms_days;

        return $this;
    }
}
