<?php

namespace App\Components\Photos;

use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Photos\Services\PhotosService;
use Illuminate\Support\ServiceProvider;

/**
 * Class PhotosServiceProvider
 *
 * @package App\Components\Photos
 */
class PhotosServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        PhotosServiceInterface::class => PhotosService::class,
    ];
}
