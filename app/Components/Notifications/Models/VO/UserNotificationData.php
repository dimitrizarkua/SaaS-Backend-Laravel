<?php

namespace App\Components\Notifications\Models\VO;

use App\Core\JsonModel;
use Carbon\Carbon;

/**
 * Class UserNotificationData
 *
 * @package App\Components\Jobs\Models\VO
 */
class UserNotificationData extends JsonModel
{
    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $body;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $expires_at;

    /**
     * UserNotificationData constructor.
     *
     * @param array|null $properties
     *
     * @throws \JsonMapper_Exception
     */
    public function __construct(?array $properties = null)
    {
        parent::__construct($properties);

        $this->expires_at = (new Carbon())::now()
            ->addDays(3);
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $userId
     *
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     */
    public function setUserId(int $userId): self
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getExpiresAt(): Carbon
    {
        return $this->expires_at;
    }

    /**
     * @param string $expiresAt
     *
     * @return \App\Components\Notifications\Models\VO\UserNotificationData
     */
    public function setExpiresAt(string $expiresAt): self
    {
        $this->expires_at = new Carbon($expiresAt);

        return $this;
    }
}
