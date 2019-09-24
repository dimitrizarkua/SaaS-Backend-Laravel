<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Enums\ModelChangedEventTypes;
use App\Events\AbstractModelChangedBroadcastEvent;

/**
 * Class JobModelChanged
 *
 * @package App\Components\Jobs\Events
 */
class JobModelChanged extends AbstractModelChangedBroadcastEvent
{
    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return Job::class;
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
            'job-%s',
            $this->targetId
        );
    }
}
