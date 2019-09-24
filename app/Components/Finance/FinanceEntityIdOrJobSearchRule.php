<?php

namespace App\Components\Finance;

use ScoutElastic\SearchRule;

/**
 * Class FinanceEntityIdOrJobSearchRule
 *
 * @package App\Components\Finance
 */
class FinanceEntityIdOrJobSearchRule extends SearchRule
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
                'bool' => [
                    'should' => [
                        ['terms' => ['id' => $query]],
                        ['terms' => ['job_id' => $query]],
                    ],
                ],

            ],
            'should' => [
                'boosting' => [
                    'positive'       => [
                        'term' => [
                            'id' => [
                                'value' => $query,
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
