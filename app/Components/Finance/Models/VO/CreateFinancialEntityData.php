<?php

namespace App\Components\Finance\Models\VO;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class CreateFinancialEntityData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreateFinancialEntityData extends JsonModel
{
    /**
     * @var int
     */
    public $location_id;
    /**
     * @var int
     */
    public $recipient_contact_id;
    /**
     * @var null|string
     */
    public $recipient_address;
    /**
     * @var null|string
     */
    public $recipient_name;
    /**
     * @var int|null
     */
    public $job_id;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $date;

    /**
     * @var FinancialEntityItemData[]
     */
    public $items = [];

    public function setDate($date): self
    {
        if (is_string($date)) {
            $date = new Carbon($date);
        }

        $this->date = $date;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        $result['recipient_address'] = $this->getRecipientAddress();
        $result['recipient_name']    = $this->getRecipientName();

        unset($result['items']);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @inheritDoc
     */
    public function getLocationId(): int
    {
        return $this->location_id;
    }

    /**
     * @return string
     */
    public function getRecipientAddress(): string
    {
        if (null === $this->recipient_address) {
            $address = $this->getContact()->getAddress();
            if (null === $address) {
                throw new NotAllowedException('Recipient contact should has at least one attached address');
            }

            $this->recipient_address = $address->full_address;
        }

        return $this->recipient_address;
    }

    /**
     * @return string
     */
    public function getRecipientName(): string
    {
        if (null === $this->recipient_name) {
            $contact              = $this->getContact();
            $this->recipient_name = $contact->getContactName();
        }

        return $this->recipient_name;
    }

    /**
     * @return Contact
     */
    private function getContact(): Contact
    {
        return Contact::findOrFail($this->recipient_contact_id);
    }

    /**
     * @return FinancialEntityItemData[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
