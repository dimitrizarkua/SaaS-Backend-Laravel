<?php

namespace App\Components\Operations;

use ScoutElastic\SearchRule;

/**
 * Class StaffSearchRule
 *
 * @package App\Components\Operations
 */
class StaffSearchRule extends SearchRule
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
            'filter' => [
                'term' => [
                    'location_ids' => $query['location_id'],
                ],
            ],
        ];
    }
}
