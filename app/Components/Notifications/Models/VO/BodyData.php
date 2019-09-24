<?php

namespace App\Components\Notifications\Models\VO;

use App\Components\Photos\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class BodyData
 *
 * @package App\Components\Jobs\Models\VO
 */
class BodyData implements Arrayable
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var User
     */
    private $sender;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var array
     */
    private $target = [];

    /**
     * BodyData constructor.
     *
     * @param string           $text
     * @param \App\Models\User $sender
     */
    public function __construct(string $text = null, User $sender = null)
    {
        $this->text   = $text;
        $this->sender = $sender;
    }

    /**
     * @return array
     */
    public function getTarget(): array
    {
        return $this->target ?? [];
    }

    /**
     * @param int    $targetId   Target identifier.
     * @param string $targetType Target type.
     *
     * @return \App\Components\Notifications\Models\VO\BodyData
     */
    public function setTarget(int $targetId, string $targetType): self
    {
        $this->target['id']   = $targetId;
        $this->target['type'] = $targetType;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context ?? [];
    }

    /**
     * @param int    $contextId   Context identifier.
     * @param string $contextType Context type.
     *
     * @return \App\Components\Notifications\Models\VO\BodyData
     */
    public function setContext(int $contextId, string $contextType): self
    {
        $this->context['id']   = $contextId;
        $this->context['type'] = $contextType;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getSender(): ?User
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     *
     * @return BodyData
     */
    public function setSender(User $sender = null): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return BodyData
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $body = ['text' => $this->getText()];

        $target = $this->getTarget();
        if (!empty($target)) {
            $body['target'] = $target;
        }

        $context = $this->getContext();
        if (!empty($context)) {
            $body['context'] = $context;
        }

        /** @var User $sender */
        $sender = $this->getSender();

        if (null !== $sender) {
            $body['sender'] = [
                'first_name' => $sender->first_name,
                'last_name'  => $sender->last_name,
            ];

            $body['sender']['avatar']['url'] = null;
            if (isset($sender->avatar) && isset($sender->avatar->thumbnails)) {
                /** @var Photo $avatarThumbnail */
                $avatarThumbnail = $sender->avatar->thumbnails->filter(function (Photo $thumb) {
                    return $thumb->width === 50;
                })->first();

                if (null !== $avatarThumbnail) {
                    $body['sender']['avatar']['url'] = $avatarThumbnail->url;
                }
            }
        }

        return $body;
    }
}
