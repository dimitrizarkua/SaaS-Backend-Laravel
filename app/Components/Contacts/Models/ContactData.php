<?php

namespace App\Components\Contacts\Models;

use App\Core\JsonModel;

/**
 * Class ContactData
 *
 * @package App\Components\Contacts\Models
 */
class ContactData extends JsonModel
{
    /**
     * Contact type.
     *
     * @var string
     */
    public $contact_type;

    /**
     * Contact category id.
     *
     * @var int
     */
    public $contact_category_id;

    /**
     * Email.
     *
     * @var string|null
     */
    public $email = null;

    /**
     * Business phone.
     *
     * @var string|null
     */
    public $business_phone = null;

    /**
     * @return string
     */
    public function getContactType(): string
    {
        return $this->contact_type;
    }

    /**
     * @param string $contact_type
     *
     * @return self
     */
    public function setContactType(string $contact_type): self
    {
        $this->contact_type = $contact_type;

        return $this;
    }

    /**
     * @return int
     */
    public function getContactCategoryId(): int
    {
        return $this->contact_category_id;
    }

    /**
     * @param int $contact_category_id
     *
     * @return self
     */
    public function setContactCategoryId(int $contact_category_id): self
    {
        $this->contact_category_id = $contact_category_id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return self
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBusinessPhone(): ?string
    {
        return $this->business_phone;
    }

    /**
     * @param string|null $business_phone
     *
     * @return self
     */
    public function setBusinessPhone(?string $business_phone): self
    {
        $this->business_phone = $business_phone;

        return $this;
    }
}
