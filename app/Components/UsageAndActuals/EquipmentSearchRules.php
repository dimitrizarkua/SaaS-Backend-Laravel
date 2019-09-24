<?php

namespace App\Components\UsageAndActuals;

use ScoutElastic\SearchRule;

/**
 * Class EquipmentSearchRules
 *
 * @package App\Components\UsageAndActuals
 */
class EquipmentSearchRules extends SearchRule
{
    /**
     * This method returns an array, that represents bool query.
     *
     * @return array
     */
    public function buildQueryPayload(): array
    {
        /** @var array $query */
        $query = $this->builder->query;

        return [
            'must' => [
                'bool' => [
                    'should' => [
                        'multi_match' => [
                            'query'  => $query['term'],
                            'type'   => 'cross_fields',
                            'fields' => [
                                'make',
                                'model',
                                'barcode',
                                'serial_number',
                                'category_name',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
