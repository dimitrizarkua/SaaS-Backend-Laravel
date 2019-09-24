<?php

namespace App\Components\Messages\Models;

use App\Core\Validatable;

/**
 * Class MessageParticipantData
 *
 * @package App\Components\Messages\Models
 */
class MessageParticipantData
{
    use Validatable;

    /**
     * Message participant address.
     *
     * @var string
     */
    private $address;

    /**
     * Message participant name.
     *
     * @var string|null
     */
    private $name;

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return self
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getValidationRules(): array
    {
        return [
            'address' => ['required', 'email'],
            'name'    => 'string',
        ];
    }

    /**
     * MessageRecipientData constructor.
     *
     * @param string      $address Message recipient address.
     * @param null|string $name    Message recipient name.
     */
    public function __construct(string $address, ?string $name = null)
    {
        $this->setAddress($address)
            ->setName($name);
    }
}
