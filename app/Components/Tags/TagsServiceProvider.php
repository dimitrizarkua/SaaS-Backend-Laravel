<?php

namespace App\Components\Tags;

use App\Components\Tags\Interfaces\TagsServiceInterface;
use App\Components\Tags\Services\TagsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class TagsServiceProvider
 *
 * @package App\Components\Tags
 */
class TagsServiceProvider extends ServiceProvider
{
    public $bindings = [
        TagsServiceInterface::class => TagsService::class,
    ];
}
