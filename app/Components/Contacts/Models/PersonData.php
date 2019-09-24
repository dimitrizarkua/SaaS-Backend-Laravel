<?php

namespace App\Components\Contacts\Models;

/**
 * Class PersonData
 *
 * @package App\Components\Contacts\Models
 */
class PersonData extends ContactData
{
    /**
     * First name.
     *
     * @var string
     */
    public $first_name;

    /**
     * Last name.
     *
     * @var string
     */
    public $last_name;

    /**
     * Job title.
     *
     * @var string|null
     */
    public $job_title = null;

    /**
     * Direct phone.
     *
     * @var string|null
     */
    public $direct_phone = null;

    /**
     * Mobile phone.
     *
     * @var string|null
     */
    public $mobile_phone = null;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     *
     * @return self
     */
    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     *
     * @return self
     */
    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getJobTitle(): ?string
    {
        return $this->job_title;
    }

    /**
     * @param null|string $job_title
     *
     * @return self
     */
    public function setJobTitle(?string $job_title): self
    {
        $this->job_title = $job_title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirectPhone(): ?string
    {
        return $this->direct_phone;
    }

    /**
     * @param string|null $direct_phone
     *
     * @return self
     */
    public function setDirectPhone(?string $direct_phone): self
    {
        $this->direct_phone = $direct_phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMobilePhone(): ?string
    {
        return $this->mobile_phone;
    }

    /**
     * @param string|null $mobile_phone
     *
     * @return self
     */
    public function setMobilePhone(?string $mobile_phone): self
    {
        $this->mobile_phone = $mobile_phone;

        return $this;
    }
}
