<?php

namespace App\Components\Tags\Interfaces;

use App\Components\Tags\Models\Tag;

/**
 * Interface TagsServiceInterface
 *
 * @package App\Components\Tags\Interfaces
 */
interface TagsServiceInterface
{
    /**
     * Add tag to the external entity.
     *
     * @param \App\Components\Tags\Models\Tag $tag Tag to be added.
     * @param int                             $id  External entity id.
     */
    public function attachTag(Tag $tag, int $id): void;

    /**
     * Get filtered set of tags.
     *
     * @param array $options.
     *
     * @return array
     */
    public function search(array $options): array;
}
