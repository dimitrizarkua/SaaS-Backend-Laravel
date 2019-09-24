<?php

namespace App\Components\Reporting\Models;

use App\Components\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Trait HasTagsDistribution
 *
 * @package App\Components\Reporting\Models
 */
trait HasTagsDistribution
{
    /**
     * Returns list of tags with percentage used compared to all tags for list of entities.
     *
     * @param \Illuminate\Support\Collection $models
     *
     * @return array
     */
    protected function getTagsDistribution(Collection $models): array
    {
        $tags     = [];
        $untagged = 0;
        $count    = 0;
        $models->each(function (Model $model) use (&$tags, &$count, &$untagged) {
            // todo add tags relation checks
            if ($model->tags->count() === 0) {
                $untagged++;
                $count++;
            }
            $model->tags->each(function (Tag $tag) use (&$tags, &$count) {
                isset($tags[$tag->name]['count'])
                    ? $tags[$tag->name]['count'] += 1
                    : $tags[$tag->name]['count'] = 1;
                $count++;
            });
        });
        if ($untagged) {
            $tags['untagged']['count'] = $untagged;
        }

        foreach ($tags as $key => &$tag) {
            $tag['name']    = $key;
            $tag['percent'] = $count ? $tag['count'] / $count * 100 : 0;
        }

        return array_values($tags);
    }
}
