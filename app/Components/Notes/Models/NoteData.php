<?php

namespace App\Components\Notes\Models;

/**
 * Class NoteData
 *
 * @package App\Components\Notes\Models
 */
class NoteData
{
    /**
     * Note text.
     *
     * @var string
     */
    private $note;

    /**
     * Sender id (user id).
     *
     * @var integer
     */
    private $userId;

    /**
     * NoteData constructor.
     *
     * @param string   $note
     * @param int|null $userId
     */
    public function __construct(string $note, ?int $userId = null)
    {
        $this->note   = $note;
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return \App\Components\Notes\Models\NoteData
     */
    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return \App\Components\Notes\Models\NoteData
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
