<?php

namespace App\Components\Office365\Models;

use App\Core\JsonModel;

/**
 * Class UserResource
 * Resource class - representation of
 * Microsoft Graph API User resource.
 *
 * @package App\Components\Office365\Models
 */
class UserResource extends JsonModel
{
    /**
     * Users id.
     *
     * @var string|null
     */
    public $id;
    /**
     * Display name.
     *
     * @var string|null
     */
    public $displayName;
    /**
     * Last name.
     *
     * @var string|null
     */
    public $surname;
    /**
     * First name.
     *
     * @var string|null
     */
    public $givenName;
    /**
     * Users email (in same cases users email will be here).
     *
     * @var string|null
     */
    public $userPrincipalName;
    /**
     * Users email.
     *
     * @var string|null
     */
    public $mail;

    /**
     * Returns users email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        if (isset($this->mail)) {
            return $this->mail;
        }

        if (isset($this->userPrincipalName)) {
            return $this->userPrincipalName;
        }

        throw new \InvalidArgumentException('Email does not set for this user.');
    }

    /**
     * Returns first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->givenName;
    }

    /**
     * Returns last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->surname;
    }

    /**
     * Returns domain of email.
     *
     * @return string
     */
    public function getEmailDomain(): string
    {
        $email = $this->getEmail();
        list (, $domain) = explode('@', $email);

        if (null === $domain) {
            throw new \InvalidArgumentException('Wrong email address: ' . $email);
        }

        return $domain;
    }
}
