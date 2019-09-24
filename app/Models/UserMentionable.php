<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait UserMentionable
 *
 * Provides common functionality for models that can contain mentions of users in text.
 *
 * @package App\Models
 * @mixin \Eloquent
 */
trait UserMentionable
{
    /**
     * Mentions matching pattern.
     *
     * @var string
     */
    private static $mentionsRegex = '/\[USER_ID:(\d+)\]/i';

    /**
     * Parse mentions containing text and extract user IDs.
     *
     * @return array
     */
    public function extractUserIds(): array
    {
        list (, $userIds) = $this->matchUsers();

        return array_unique($userIds);
    }

    /**
     * Resolve mentions and update corresponding relationships.
     */
    public function resolveMentions()
    {
        $text = $this->getUnresolvedText();
        list ($mentions, $userIds) = $this->matchUsers();

        $users = User::whereIn('id', $userIds)
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        foreach ($mentions as $index => $mention) {
            $userId      = $userIds[$index];
            $userName    = $users[$userId] ?? '';
            $replacement = '@' . str_replace(' ', '_', $userName);

            $text = str_replace($mention, $replacement, $text);
        }

        $this->setResolvedText($text);
        $this->save();
        $this->mentionedUsers()->sync(array_keys($users));
    }

    /**
     * Define mentioned users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mentionedUsers(): BelongsToMany
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    /**
     * Return text with unresolved mentions.
     *
     * @return string
     */
    protected function getUnresolvedText(): string
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    /**
     * Set text with resolved mentions.
     *
     * @param string $text
     *
     * @return string
     */
    protected function setResolvedText(string $text)
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    /**
     * @return array
     */
    private function matchUsers(): array
    {
        $text    = $this->getUnresolvedText();
        $matches = [];

        preg_match_all(self::$mentionsRegex, $text, $matches, PREG_PATTERN_ORDER);

        return $matches;
    }
}
