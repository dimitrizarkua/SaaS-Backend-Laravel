<?php

namespace App\Components\Search\SearchRules;

use ScoutElastic\SearchRule;

/**
 * Class UsersAndTeamsRule
 *
 * @package App\Components\Search\SearchRules
 */
class UsersAndTeamsRule extends SearchRule
{
    /**
     * This method returns an array, that represents bool query.
     *
     * @return array
     */
    public function buildQueryPayload()
    {
        /** @var array $query */
        $query = $this->builder->query;

        return [
            'must'   => [
                'match' => [
                    'name' => $query['term'],
                ],
            ],
            'should' => [
                'boosting' => [
                    'positive'       => [
                        'terms' => [
                            'location_ids' => $query['locationIds'],
                        ]
                    ],
                    'negative'       => [
                        'term' => [
                            'location_ids' => [
                                'value' => 0,
                            ],
                        ]
                    ],
                    'negative_boost' => 0,
                ],
            ],
        ];
    }
}
