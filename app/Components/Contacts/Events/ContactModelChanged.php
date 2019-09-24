<?php

namespace App\Components\Contacts\Events;

use App\Components\Contacts\Models\Contact;
use App\Enums\ModelChangedEventTypes;
use App\Events\AbstractModelChangedBroadcastEvent;

/**
 * Class ContactModelChanged
 *
 * @package App\Components\Contacts\Events
 */
class ContactModelChanged extends AbstractModelChangedBroadcastEvent
{
    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return Contact::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return ModelChangedEventTypes::UPDATED;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelName()
    {
        return sprintf(
            'contact-%s',
            $this->targetId
        );
    }
}
