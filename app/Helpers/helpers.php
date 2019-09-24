<?php

if (!function_exists('taggedCache')) {
    /**
     * Return tagged cache instance
     *
     * @param string|array $tag
     *
     * @return null|\Illuminate\Cache\TaggedCache
     *
     */
    function taggedCache($tag)
    {
        if (!is_array($tag) && !is_string($tag)) {
            return null;
        }

        $def = [env('APP_ENV')];

        if (!is_array($tag)) {
            $tag = [$tag];
        }

        if (array_intersect($def, $tag)) {
            return null;
        }

        return Cache::tags(array_merge($def, $tag));
    }
}

if (!function_exists('mapElasticResult')) {
    /**
     * Map elastic result.
     *
     * @param array $results Elastic search results.
     *
     * @return array
     */
    function mapElasticResults($results)
    {
        $hits = (int)array_get($results, 'hits.total');
        if (0 === $hits) {
            return [];
        }

        return collect($results['hits']['hits'])
            ->pluck('_source')
            ->toArray();
    }
}

if (!function_exists('arrayMapKeys')) {
    /**
     * Map array keys.
     *
     * @param array    $data     Array to be formatted.
     * @param callable $callback Callback function that will be applied for each key.
     *
     * @return array
     */
    function arrayMapKeys(array $data, callable $callback)
    {
        return array_combine(
            array_map($callback, array_keys($data)),
            array_values($data)
        );
    }
}
if (!function_exists('castToDollars')) {
    /**
     * Gets number and returns formatted currency string.
     *
     * @param float $dollars
     *
     * @return string
     */
    function castToDollars(float $dollars): string
    {
        return '$' . number_format(sprintf('%0.2f', preg_replace('/[^0-9.]/', '', $dollars)), 2);
    }
}
