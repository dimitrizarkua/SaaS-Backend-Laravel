<?php

namespace App\Components\Jobs;

use ScoutElastic\SearchRule;

/**
 * Class JobTasksSearchRules
 *
 * @package App\Components\Jobs
 */
class JobTasksSearchRules extends SearchRule
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
                    'should'   => [
                        ['match' => ['name' => $query['term']]],
                        ['match_phrase_prefix' => ['job_id' => $query['term']]],
                    ],
                    'must_not' => [
                        ['exists' => ['field' => 'job_run_id']],
                    ],
                ],
            ],
            'filter' => [
                ['match' => ['location_id' => $query['location_id']]],
            ],
        ];
    }
}
