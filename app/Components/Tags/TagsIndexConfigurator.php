<?php

namespace App\Components\Tags;

use App\DefaultIndexConfigurator;

/**
 * Class TagsIndexConfigurator
 *
 * @package App\Components\Tags
 */
class TagsIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'tags_index';
}
