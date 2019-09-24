<?php

namespace App\Components\Notifications\Events;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserNotificationEvent
 *
 * @package App\Components\Notifications\Events
 */
abstract class UserNotificationEvent
{

    /** @var Model */
    public $targetModel;

    /** @var Model */
    public $contextModel;

    /** @var int User identifier who send notification */
    public $senderId;

    /**
     * Returns notification sender with avatar.
     *
     * @return \App\Models\User|null
     */
    public function getSender(): ?User
    {
        // todo optimize select
        return User::with('avatar.thumbnails')
            ->find($this->senderId);
    }

    /**
     * Returns notification type.
     *
     * @return string
     */
    abstract public function getNotificationType(): string;

    /**
     * Returns target type.
     *
     * @return string
     */
    public function getTargetType(): string
    {
        $tableName = str_singular($this->targetModel->getTable());

        return str_ireplace('_', ' ', $tableName);
    }

    /**
     * Returns target id.
     *
     * @return int
     */
    public function getTargetId(): int
    {
        return $this->targetModel->id;
    }

    /**
     * Returns resolved text for event.
     *
     * @param \App\Models\User $recipient
     *
     * @return string
     */
    abstract public function getBodyText(User $recipient): string;

    /**
     * Returns context entity id.
     *
     * @return int|null
     */
    public function getContextId(): ?int
    {
        return isset($this->contextModel)
            ? $this->contextModel->id
            : null;
    }

    /**
     * Returns context type. f.i. note, message in the job.
     *
     * @return string
     */
    public function getContextType(): string
    {
        return isset($this->contextModel)
            ? str_singular($this->contextModel->getTable())
            : '';
    }
}
