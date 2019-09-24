<?php

namespace App\Components\Users;

use ScoutElastic\SearchRule;

/**
 * Class UsersSearchForMentionsRule
 *
 * @package App\Components\Users
 */
class UsersSearchForMentionsRule extends SearchRule
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
                    'full_name' => $query['name'],
                ],
            ],
            'should' => [
                'boosting' => [
                    'positive'       => [
                        'terms' => [
                            'location_ids' => $query['location_ids'],
                        ]
                    ],
                    // Boosting query requires 'negative' query to be set
                    'negative'       => [
                        'term' => [
                            'location_ids' => [
                                'value' => 0,
                            ],
                        ]
                    ],
                    // Boosting query requires 'negative_boost' to be set to be a positive value
                    'negative_boost' => 0,
                ],
            ],
        ];
    }
}
