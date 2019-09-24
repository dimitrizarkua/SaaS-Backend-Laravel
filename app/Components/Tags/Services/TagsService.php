<?php

namespace App\Components\Tags\Services;

use App\Components\Tags\Enums\TagsEndpointsLimits;
use App\Components\Tags\Interfaces\TagsServiceInterface;
use App\Components\Tags\Mappers\TagTypesMapper;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use App\Exceptions\Api\NotFoundException;

/**
 * Class TagsService
 *
 * @package App\Components\Tags\Services
 */
class TagsService implements TagsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function attachTag(Tag $tag, int $id): void
    {
        $className = TagTypesMapper::getMapping($tag->type);
        if (!$className) {
            throw new \RuntimeException('Unknown tag type');
        }
        $entity = $className::find($id);

        if (null === $entity) {
            throw new NotFoundException(
                sprintf('%s with the specified id could not be found', ucfirst($tag->type))
            );
        }

        try {
            $entity->tags()->attach($tag->id);
        } catch (\Exception $exception) {
            throw new NotAllowedException(
                sprintf('This tag is already attached to the %s', ucfirst($tag->type))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $options): array
    {
        if (!isset($options['count'])) {
            $options['count'] = TagsEndpointsLimits::DEFAULT_TAGS_COUNT;
        }
        if ($options['count'] > TagsEndpointsLimits::MAX_TAGS_COUNT) {
            $options['count'] = TagsEndpointsLimits::MAX_TAGS_COUNT;
        }

        $results = Tag::filter($options)
            ->raw();

        return mapElasticResults($results);
    }
}
