<?php

namespace App\Components\Documents;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Documents\Services\DocumentsService;
use Illuminate\Support\ServiceProvider;

/**
 * Class DocumentServiceProvider
 *
 * @package App\Providers
 */
class DocumentServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        DocumentsServiceInterface::class => DocumentsService::class,
    ];
}
