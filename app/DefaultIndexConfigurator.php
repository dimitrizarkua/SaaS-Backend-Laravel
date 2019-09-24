<?php

namespace App;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

/**
 * Class DefaultIndexConfigurator
 *
 * @package App
 */
class DefaultIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    /**
     * @var array
     */
    protected $settings = [
        'analysis' => [
            'analyzer'   => [
                'autocomplete'        => [
                    'tokenizer' => 'autocomplete',
                    'filter'    => [
                        'lowercase',
                    ],
                ],
                'autocomplete_search' => [
                    'tokenizer' => 'lowercase',
                ],
            ],
            'tokenizer'  => [
                'autocomplete' => [
                    'type'        => 'edge_ngram',
                    'min_gram'    => 1,
                    'max_gram'    => 15,
                    'token_chars' => [
                        'letter',
                        'digit',
                    ],
                ],
            ],
            'normalizer' => [
                'case_insensitive' => [
                    'filter' => 'lowercase',
                ],
            ],
        ],
    ];
}
