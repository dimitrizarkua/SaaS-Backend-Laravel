<?php

namespace App\Components\Finance;

use ScoutElastic\SearchRule;

/**
 * Class FinanceEntitySearchRule
 *
 * @package App\Components\Finance
 */
class FinanceEntitySearchRule extends SearchRule
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
                'match_phrase_prefix' => [
                    'id' => $query['id'],
                ],
            ],
            'should' => [
                'boosting' => [
                    'positive'       => [
                        'term' => [
                            'id' => [
                                'value' => $query['id'],
                            ],
                        ],
                    ],
                    // Boosting query requires 'negative' query to be set
                    'negative'       => [
                        'term' => [
                            'id' => [
                                'value' => 0,
                            ],
                        ],
                    ],
                    // Boosting query requires 'negative_boost' to be set to be a positive value
                    'negative_boost' => 0,
                ],
            ],
        ];
    }
}
