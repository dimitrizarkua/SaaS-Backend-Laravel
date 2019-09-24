<?php

namespace App\Components\Meetings\Models;

use App\Core\JsonModel;

/**
 * Class MeetingData
 *
 * @package App\Components\Meetings\Models
 */
class MeetingData extends JsonModel
{
    /**
     * Title.
     *
     * @var string
     */
    public $title;

    /**
     * Scheduled at.
     *
     * @var string
     */
    public $scheduled_at;

    /**
     * User id.
     *
     * @var
     */
    public $user_id;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getScheduledAt(): string
    {
        return $this->scheduled_at;
    }

    /**
     * @param string $scheduled_at
     */
    public function setScheduledAt(string $scheduled_at): void
    {
        $this->scheduled_at = $scheduled_at;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }
}
